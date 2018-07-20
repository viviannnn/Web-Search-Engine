import java.util.StringTokenizer;
import java.util.Map;
import java.util.HashMap;
import java.io.File;
import java.io.*;


/** This is the parallel version of WordCount.java by way of
 * WordCountParallelBad.java.  The key change is that we use a
 * concurrent hashmap to allow parallel accesses, rather than a
 * synchronized HashMap.  Notice the updateCount() function needs be
 * concerned with maintaining the count correctly.
 */

import java.util.concurrent.ConcurrentMap;
import java.util.concurrent.ConcurrentHashMap;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.concurrent.TimeUnit;

import org.apache.tika.Tika;
import org.apache.tika.exception.TikaException;



public class WordCountParallel implements Runnable{
	private final String buffer;
	private final ConcurrentMap<String,Integer> counts;
	public WordCountParallel(String buffer, ConcurrentMap<String,Integer> counts) {
		this.counts = counts;
		this.buffer = buffer;
	}
	private final static String DELIMS = " :;,.{}()\t\n%\"'/©!£×@#$%^&*?+=-_~<>[]|";
//    private final static boolean printAll = false;
    /**
     * Looks for the last delimiter in the string, and returns its
     * index.
     */
    private static int findDelim(String buf) {
        for (int i = buf.length() - 1; i>=0; i--) {
            for (int j = 0; j < DELIMS.length(); j++) {
                char d = DELIMS.charAt(j);
                if (d == buf.charAt(i)) return i;
            }
        }
        return 0;
    }
    /** 
     * Reads in a chunk of the file into a string.  
     */
    private static String readString(String content, int size)
            throws java.io.IOException 
        {
            StringBuffer fileData = new StringBuffer(size);
            int numRead=0;

            while(size > 0) {
                int bufsz = 1024 > size ? size : 1024;
                char[] buf = new char[bufsz];
                buf = content.substring(0,bufsz).toCharArray();
                
                numRead = buf.length;
                if (numRead == 0)
                    break;
                String readData = String.valueOf(buf, 0, numRead);
                fileData.append(readData);
                size -= numRead;
            }
            return fileData.toString();
        }

        /**
         * Updates the count for each number of words.  Uses optimistic
         * techniques to make sure count is updated properly.
         */
    private void updateCount(String q) {
        Integer oldVal, newVal;
        Integer cnt = counts.get(q);
        // first case: there was nothing in the table yet
        if (cnt == null) {
            // attempt to put 1 in the table.  If the old
            // value was null, then we are OK.  If not, then
            // some other thread put a value into the table
            // instead, so we fall through
            oldVal = counts.put(q, 1);
            if (oldVal == null) return;
        }
        // general case: there was something in the table
        // already, so we have increment that old value
        // and attempt to put the result in the table.
        // To make sure that we do this atomically,
        // we use concurrenthashmap's replace() method
        // that takes both the old and new value, and will
        // only replace the value if the old one currently
        // there is the same as the one passed in.
        // Cf. http://www.javamex.com/tutorials/synchronization_concurrency_8_hashmap2.shtml 
        do {
            oldVal = counts.get(q);
            newVal = (oldVal == null) ? 1 : (oldVal + 1);
        } while (!counts.replace(q, oldVal, newVal));
    } 

    /**
     * Main task : tokenizes the given buffer and counts words. 
     */
    public void run() {
        StringTokenizer st = new StringTokenizer(buffer,DELIMS);
        while (st.hasMoreTokens()) {
            String token = st.nextToken();
            //System.out.println("updating count for "+token);
            updateCount(token);
        }
    } 
    public static void main(String args[]) throws java.io.IOException, TikaException { 
//        long startTime = System.currentTimeMillis();
//        if (args.length < 1) {
//            System.out.println("Usage: <file to count> [#threads] [chunksize]");
//            System.exit(1);
//        }
          int numThreads = 4;
          int chunksize = 1000;
//        if (args.length >= 2)
//            numThreads = Integer.valueOf(args[1]);
//        if (args.length >= 3)
//            chunksize = Integer.valueOf(args[2]);
        ExecutorService pool = Executors.newFixedThreadPool(numThreads);
        ConcurrentMap<String,Integer> m = new ConcurrentHashMap<String,Integer>();
        
        Tika tika = new Tika();
        String leftover = ""; // in case a string broken in half
        
        String dirPath = "/Users/Vivian_HW/Desktop/cs572/NYTimesBBCNewsDownloadData";
		File dir = new File(dirPath);
		for(File file : dir.listFiles()){
			if (file.getName().equals(".DS_Store")) {
				continue;
			}
			String filecontent = tika.parseToString(file);
			filecontent = filecontent.toLowerCase();
//			System.out.println(filecontent);

			String first = filecontent;
			String rest = filecontent;
			while (true) {	
				int size = first.length() < chunksize ? first.length() : chunksize;
				first = filecontent.substring(0,size);
				filecontent = rest.substring(size);

				rest = filecontent;

				if (first.length() == 0) break;
//				System.out.println(first);
				
	            if (first.equals("")) {
//	            	System.out.println(1);
	                if (!leftover.equals("")) 
	                    new WordCountParallel(leftover,m).run();
	                break;
	            }
	            int idx = findDelim(first);
	
	            String taskstr = leftover + first.substring(0,idx);
	            leftover = first.substring(idx,first.length());
	            first = filecontent;
//	            System.out.println(first);
	            pool.submit(new WordCountParallel(taskstr,m));
			}
		}
	        pool.shutdown();
	        try {
	            pool.awaitTermination(1,TimeUnit.DAYS);
	        } catch (InterruptedException e) {
	            System.out.println("Pool interrupted!");
	            System.exit(1);
	        }
		
//        long endTime = System.currentTimeMillis();
//        long elapsed = endTime - startTime;
//        int total = 0;
	    PrintWriter writer = new PrintWriter(new BufferedWriter(new FileWriter(
					"/Users/Vivian_HW/Desktop/cs572/big.txt")));
        PrintWriter writerA = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/a.txt")));
        PrintWriter writerB = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/b.txt")));
        PrintWriter writerC = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/c.txt")));
        PrintWriter writerD = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/d.txt")));
        PrintWriter writerE = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/e.txt")));
        PrintWriter writerF = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/f.txt")));
        PrintWriter writerG = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/g.txt")));
        PrintWriter writerH = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/h.txt")));
        PrintWriter writerI = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/i.txt")));
        PrintWriter writerJ = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/j.txt")));
        PrintWriter writerK = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/k.txt")));
        PrintWriter writerL = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/l.txt")));
        PrintWriter writerM = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/m.txt")));
        PrintWriter writerN = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/n.txt")));
        PrintWriter writerO = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/o.txt")));
        PrintWriter writerP = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/p.txt")));
        PrintWriter writerQ = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/q.txt")));
        PrintWriter writerR = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/r.txt")));
        PrintWriter writerS = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/s.txt")));
        PrintWriter writerT = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/t.txt")));
        PrintWriter writerU = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/u.txt")));
        PrintWriter writerV = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/v.txt")));
        PrintWriter writerW = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/w.txt")));
        PrintWriter writerX = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/x.txt")));
        PrintWriter writerY = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/y.txt")));
        PrintWriter writerZ = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/z.txt")));
        PrintWriter writer0 = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/0.txt")));
        PrintWriter writer1 = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/1.txt")));
        PrintWriter writer2 = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/2.txt")));
        PrintWriter writer3 = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/3.txt")));
        PrintWriter writer4 = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/4.txt")));
        PrintWriter writer5 = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/5.txt")));
        PrintWriter writer6 = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/6.txt")));
        PrintWriter writer7 = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/7.txt")));
        PrintWriter writer8 = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/8.txt")));
        PrintWriter writer9 = new PrintWriter(new BufferedWriter(new FileWriter(
				"/Users/Vivian_HW/Desktop/cs572/parser/9.txt")));
        
        for (Map.Entry<String,Integer> entry : m.entrySet()) {
        	writer.write(entry.getKey()+' '+entry.getValue()+"\n");
        	
        	if (entry.getKey().charAt(0) =='a')
        		writerA.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) =='b')
        		writerB.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) =='c')
        		writerC.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) =='d')
        		writerD.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) =='e')
        		writerE.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) =='f')
        		writerF.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) =='g')
        		writerG.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) =='h')
        		writerH.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) =='i')
        		writerI.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) =='j')
        		writerJ.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) =='k')
        		writerK.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) =='l')
        		writerL.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) =='m')
        		writerM.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) =='n')
        		writerN.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) =='o')
        		writerO.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) =='p')
        		writerP.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) =='q')
        		writerQ.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) =='r')
        		writerR.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) =='s')
        		writerS.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) =='t')
        		writerT.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) =='u')
        		writerU.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) =='v')
        		writerV.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) =='w')
        		writerW.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) =='x')
        		writerX.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) =='y')
        		writerY.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) =='z')
        		writerZ.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) == '0')
        		writer0.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) == '1')
        		writer1.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) == '2')
        		writer2.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) == '3')
        		writer3.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) == '4')
        		writer4.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) == '5')
        		writer5.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) == '6')
        		writer6.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) == '7')
        		writer7.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) == '8')
        		writer8.write(entry.getKey()+' '+entry.getValue()+"\n");
        	if (entry.getKey().charAt(0) == '9')
        		writer9.write(entry.getKey()+' '+entry.getValue()+"\n");
        	
        	
        	
        	
//            int count = entry.getValue();
//            if (printAll)
//                System.out.format("%-30s %d\n",entry.getKey(),count);
//            total += count;
        }
       writer.close();
       writerA.close();
       writerB.close();
       writerC.close();
       writerD.close();
       writerE.close();
       writerF.close();
       writerG.close();
       writerH.close();
       writerI.close();
       writerJ.close();
       writerK.close();
       writerL.close();
       writerM.close();
       writerN.close();
       writerO.close();
       writerP.close();
       writerQ.close();
       writerR.close();
       writerS.close();
       writerT.close();
       writerU.close();
       writerV.close();
       writerW.close();
       writerX.close();
       writerY.close();
       writerZ.close();
       writer0.close();
       writer1.close();
       writer2.close();
       writer3.close();
       writer4.close();
       writer5.close();
       writer6.close();
       writer7.close();
       writer8.close();
       writer9.close();
       
       

//        System.out.println("Total words = "+total);
//        System.out.println("Total time = "+elapsed+" ms");
		
    }
}
