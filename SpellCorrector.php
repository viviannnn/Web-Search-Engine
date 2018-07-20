<?php

/*
*************************************************************************** 
*   Copyright (C) 2008 by Felipe Ribeiro                                  * 
*   felipernb@gmail.com                                                   * 
*   http://www.feliperibeiro.com                                          * 
*                                                                         * 
*   Permission is hereby granted, free of charge, to any person obtaining * 
*   a copy of this software and associated documentation files (the       * 
*   "Software"), to deal in the Software without restriction, including   * 
*   without limitation the rights to use, copy, modify, merge, publish,   * 
*   distribute, sublicense, and/or sell copies of the Software, and to    * 
*   permit persons to whom the Software is furnished to do so, subject to * 
*   the following conditions:                                             * 
*                                                                         * 
*   The above copyright notice and this permission notice shall be        * 
*   included in all copies or substantial portions of the Software.       * 
*                                                                         * 
*   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,       * 
*   EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF    * 
*   MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.* 
*   IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR     * 
*   OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, * 
*   ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR * 
*   OTHER DEALINGS IN THE SOFTWARE.                                       * 
*************************************************************************** 
*/ 


/**
 * This class implements the Spell correcting feature, useful for the 
 * "Did you mean" functionality on the search engine. Using a dicionary of words
 * extracted from the product catalog.
 * 
 * Based on the concepts of Peter Norvig: http://norvig.com/spell-correct.html
 * 
 * @author Felipe Ribeiro <felipernb@gmail.com>
 * @date September 18th, 2008
 * @package catalog
 *
 */
class SpellCorrector {
	private static $NWORDS;
	
	/**
	 * Reads a text and extracts the list of words
	 *
	 * @param string $text
	 * @return array The list of words
	 */
	private static function  words($text) {
		$matches = array();
		preg_match_all("/[a-z]+/",strtolower($text),$matches);
		return $matches[0];
	}
	
	/**
	 * Creates a table (dictionary) where the word is the key and the value is it's relevance 
	 * in the text (the number of times it appear)
	 *
	 * @param array $features
	 * @return array
	 */
	private static function train(array $features) {
		$model = array();
		$count = count($features);
		for($i = 0; $i<$count; $i++) {
			$f = $features[$i];
			$model[$f] +=1;
		}
		return $model;
	}
	
	/**
	 * Generates a list of possible "disturbances" on the passed string
	 *
	 * @param string $word
	 * @return array
	 */
	private static function edits1($word) {
		
		$alphabet = 'abcdefghijklmnopqrstuvwxyz';
		$alphabet = str_split($alphabet);
		$n = strlen($word);
		$edits = array();
		for($i = 0 ; $i<$n;$i++) {
			$edits[] = substr($word,0,$i).substr($word,$i+1); 		//deleting one char
			foreach($alphabet as $c) {
				$edits[] = substr($word,0,$i) . $c . substr($word,$i+1); //substituting one char
			}
		}
		for($i = 0; $i < $n-1; $i++) {
			$edits[] = substr($word,0,$i).$word[$i+1].$word[$i].substr($word,$i+2); //swapping chars order
		}
		for($i=0; $i < $n+1; $i++) {
			foreach($alphabet as $c) {
				$edits[] = substr($word,0,$i).$c.substr($word,$i); //inserting one char
			}
		}

		return $edits;
	}
	
	/**
	 * Generate possible "disturbances" in a second level that exist on the dictionary
	 *
	 * @param string $word
	 * @return array
	 */
	private static function known_edits2($word) {
		$known = array();
		foreach(self::edits1($word) as $e1) {
			foreach(self::edits1($e1) as $e2) {
				if(array_key_exists($e2,self::$NWORDS)) $known[] = $e2;				
			}
		}
		return $known;
	}
	
	/**
	 * Given a list of words, returns the subset that is present on the dictionary
	 *
	 * @param array $words
	 * @return array
	 */
	private static function known(array $words) {
		$known = array();
		foreach($words as $w) {
			if(array_key_exists($w,self::$NWORDS)) {
				$known[] = $w;

			}
		}
		return $known;
	}
	
	
	/**
	 * Returns the word that is present on the dictionary that is the most similar (and the most relevant) to the
	 * word passed as parameter, 
	 *
	 * @param string $word
	 * @return string
	 */
	public static function correct($word) {
		echo $word;

		$word = trim($word);
		if(empty($word)) return;
		
		$word = strtolower($word);
		$arr = $word[0];
		
		if ($arr == 'a')
			{$fp = fopen("./parser/a.txt","r");}
		else if ($arr == 'b')
			{$fp = fopen("./parser/b.txt","r");}
		else if ($arr == 'c')
			{$fp = fopen("./parser/c.txt","r");}
		else if ($arr == 'd')
			{$fp = fopen("./parser/d.txt","r");}
		else if ($arr == 'e')
			{$fp = fopen("./parser/e.txt","r");}
		else if ($arr == 'f')
			{$fp = fopen("./parser/f.txt","r");}
		else if ($arr == 'g')
			{$fp = fopen("./parser/g.txt","r");}
		else if ($arr == 'h')
			{$fp = fopen("./parser/h.txt","r");}
		else if ($arr == 'i')
			{$fp = fopen("./parser/i.txt","r");}
		else if ($arr == 'j')
			{$fp = fopen("./parser/j.txt","r");}
		else if ($arr == 'k')
			{$fp = fopen("./parser/k.txt","r");}
		else if ($arr == 'l')
			{$fp = fopen("./parser/l.txt","r");}
		else if ($arr == 'm')
			{$fp = fopen("./parser/m.txt","r");}
		else if ($arr == 'n')
			{$fp = fopen("./parser/n.txt","r");}
		else if ($arr == 'o')
			{$fp = fopen("./parser/o.txt","r");}
		else if ($arr == 'p')
			{$fp = fopen("./parser/p.txt","r");}
		else if ($arr == 'q')
			{$fp = fopen("./parser/q.txt","r");}
		else if ($arr == 'r')
			{$fp = fopen("./parser/r.txt","r");}
		else if ($arr == 's')
			{$fp = fopen("./parser/s.txt","r");}
		else if ($arr == 't')
			{$fp = fopen("./parser/t.txt","r");}
		else if ($arr == 'u')
			{$fp = fopen("./parser/u.txt","r");}
		else if ($arr == 'v')
			{$fp = fopen("./parser/v.txt","r");}
		else if ($arr == 'w')
			{$fp = fopen("./parser/w.txt","r");}
		else if ($arr == 'x')
			{$fp = fopen("./parser/x.txt","r");}
		else if ($arr == 'y')
			{$fp = fopen("./parser/y.txt","r");}
		else if ($arr == 'z')
			{$fp = fopen("./parser/z.txt","r");}
		else if ($arr == '0')
			{$fp = fopen("./parser/0.txt","r");}
		else if ($arr == '1' )
			{$fp = fopen("./parser/1.txt","r");}
		else if ($arr == '2' )
			{$fp = fopen("./parser/2.txt","r");}
		else if ($arr == '3' )
			{$fp = fopen("./parser/3.txt","r");}
		else if ($arr == '4' )
			{$fp = fopen("./parser/4.txt","r");}
		else if ($arr == '5' )
			{$fp = fopen("./parser/5.txt","r");}
		else if ($arr == '6' )
			{$fp = fopen("./parser/6.txt","r");}
		else if ($arr == '7' )
			{$fp = fopen("./parser/7.txt","r");}
		else if ($arr == '8' )
			{$fp = fopen("./parser/8.txt","r");}
		else if ($arr == '9' )
			{$fp = fopen("./parser/9.txt","r");}

		self::$NWORDS = array();
		while(!feof($fp)){
			$line = trim(fgets($fp));
			$pieces = explode(" ", $line);
			if (count($pieces) >= 2) {
				self::$NWORDS[$pieces[0]] = $pieces[1];
			}
		
		}
		fclose($fp);
		
		
		$candidates = array(); 
		if(self::known(array($word))) {
			return $word;
		} elseif(($tmp_candidates = self::known(self::edits1($word)))) {
			foreach($tmp_candidates as $candidate) {
				$candidates[] = $candidate;
			}
		} elseif(($tmp_candidates = self::known_edits2($word))) {
			foreach($tmp_candidates as $candidate) {
				$candidates[] = $candidate;
			}
		} else {
			return $word;
		}
		
		
		$max = 0;
		foreach($candidates as $c) {
			$value = self::$NWORDS[$c];
			if( $value > $max) {
				$max = $value;
				$word = $c;
			}
		}
		return $word;
		
	}
	
	
}

?>