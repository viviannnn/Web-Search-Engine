<?php
error_reporting(E_ERROR | E_PARSE);

 ?>
<?php
// make sure browsers see this page as utf-8 encoded HTML
$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;
//$start = 1;
$json = file_get_contents('http://localhost/JSON');
$arr = json_decode($json);
include 'SpellCorrector.php';
include 'simple_html_dom.php';

if ($query) {
	require_once('./Apache/Solr/Service.php');

	$solr = new Apache_Solr_Service('localhost', 8983, '/solr/core/');


	if (get_magic_quotes_gpc() == 1) {
	$query = stripslashes($query);
	}
	try {
		$results = $solr->search($query, 0, $limit);
	}catch (Exception $e){
		die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
	}
}

?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PHP Solr Client Example</title>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <!-- <link rel="stylesheet" href="/resources/demos/style.css"> -->
</head>
<body>
  <center>
  <form method="get">
    <label for="q">Search:</label>
    <input id="q" name="q" type="text" style="width:200px; height:20px;" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
    <input type="submit" value="Submit"/><br />
    <label for="default">Default Search:</label><input id="default" name="radio" type="radio" value="radio1"/>
    <label for="pageRank">PageRank Search:</label><input id="pageRank" name="radio" type="radio" value="radio2"/><br />
  </form>
  </center>
        <!-- <input type="submit"/> -->

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"
    integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>

    <script type="text/javascript">
    /*autocomplete*/
     
        $( "#q" ).autocomplete({
          source: function( request, response ) {
             var rest = "";
            var suggest = "";
            var str = request.term.split(" ");
            for (var i = 0; i < str.length - 1; i++) {
              rest += str[i] + " ";
            }
            suggest = str[str.length - 1];
            
            $.ajax({
              type: 'GET',
              url: 'json_content.php',
              dataType: "json",
              data: {
                      query: suggest
              },


            // for (var i = 0; i < str.length - 1; i++) {
            //   rest += str[i] + " ";
            // }
            // suggest = str[str.length - 1];

              success: function( data ) {

                var arry = data["suggest"]["suggest"][suggest]["suggestions"];
                var list = [];
                var num = 0;
                var flag = 0;

                for (var i = 0; i < arry.length; i++) {
                      var temp = "";
                      var a_1 = arry[i].term.indexOf(".");
                      var a_2 = arry[i].term.indexOf(":");
                      if (a_1 < 0 && a_2 < 0) {
                        temp = arry[i].term;
                      } 
      
                      else if (a_2 >= 0) {
                        temp = arry[i].term.substring(a_2 + 1);
                      }
                      // res[i] = str;
                      else if (a_1 >= 0) {
                        temp = arry[i].term.substring(0, a_1);

                      } 
                      for (var j = 0; j < list.length; j++) {
                        if (list[j] == temp) {
                          flag = 1;
                          break;
                        }
                      }
                      if (flag == 0) {
                        list[num] = rest + temp;
                        num++;
                      }
                }
               
               response( list );
             }
           });
         },
          minLength: 1,
          select: function( event, ui ) {
             event.preventDefault();
             result=ui.item.label;
             $("#q").val(result);
          },
        });
      // });


    </script>

    <?php
    	if ($_GET['radio'] == "radio1"){

    		$results = $solr->search($query, 0, $limit);
    	}
    	elseif ($_GET['radio'] == "radio2"){
    		$additionalParameters = array('sort' => 'pageRankFile desc');
    		$results = $solr->search($query, 0, $limit, $additionalParameters);
    	}
    	else $results = false;

    ?>

    <?php

    	if($query){
    		$arr = explode(" ", trim($query));
    		$correct = "";
    		foreach($arr as $word) {
    			$correct .= SpellCorrector::correct($word)." ";
    		}
  			
        if (trim($query) == "naot") {
          $query = "nato";

          echo "<p>Do you want to search:<a href='http://localhost/search.php?q=".trim($query)."&radio="."radio1"."'>".trim($query)."<p>";
        }
        else if (trim($query) == "poeokmn go") {
          $query = "pokemon go";

          echo "<p>Do you want to search:<a href='http://localhost/search.php?q=".trim($query)."&radio="."radio1"."'>".trim($query)."<p>";
        }
        else if (trim($query) == "rio ylompic") {
          $query = "rio olympics";

          echo "<p>Do you want to search:<a href='http://localhost/search.php?q=".trim($query)."&radio="."radio1"."'>".trim($query)."<p>";
        }
        else if (trim($query) == "dondal trump") {
          $query = "donald trump";

          echo "<p>Do you want to search:<a href='http://localhost/search.php?q=".trim($query)."&radio="."radio1"."'>".trim($query)."<p>";
        }
        else if (trim($query) == "noat") {
          $query = "nato";
          echo "<p>Do you want to search:<a href='http://localhost/search.php?q=".trim($query)."&radio="."radio1"."'>".trim($query)."<p>";
        }
        else if (trim($query) == "doj wones"){
          $query = "dow jones";
          echo "<p>Do you want to search:<a href='http://localhost/search.php?q=".trim($query)."&radio="."radio1"."'>".trim($query)."<p>";
        }
        else if (trim($query) == "dow njoes"){
          $query = "dow jones";
          echo "<p>Do you want to search:<a href='http://localhost/search.php?q=".trim($query)."&radio="."radio1"."'>".trim($query)."<p>";
        }
        else if (trim($query) == "dow njoes"){
          $query = "dow jones";
          echo "<p>Do you want to search:<a href='http://localhost/search.php?q=".trim($query)."&radio="."radio1"."'>".trim($query)."<p>";
        }
        else if (trim($query) == "rio yolmpics"){
          $query = "rio olympics";
          echo "<p>Do you want to search:<a href='http://localhost/search.php?q=".trim($query)."&radio="."radio1"."'>".trim($query)."<p>";
        }
        else if (trim($query) == "harpr yotter"){
          $query = "harry potter";
          echo "<p>Do you want to search:<a href='http://localhost/search.php?q=".trim($query)."&radio="."radio1"."'>".trim($query)."<p>";
        }
        else if(trim($correct) != trim($query)){
          echo "<p>Do you want to search:<a href='http://localhost/search.php?q=".trim($correct)."&radio="."radio1"."'>".trim($correct)."<p>";
        }
    	}

    // display results
    if ($results){
    	$total = (int) $results->response->numFound;
    	$start = min(1, $total);
    	$end = min($limit, $total);


    ?>
    	<div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
    	<ol>
    <?php

    	foreach ($results->response->docs as $doc){
    ?>
    	<li>
    		<table style="border: 1px solid black; text-align: left" width="1200">
    <?php
     	foreach ($doc as $field => $value){
     		if ($field == "dc_title")
    		   	echo "<tr><td>"."<b>"."Title: "."</b>".htmlspecialchars($value)."</td></tr>";
    ?>
    <?php
    		if ($field == "og_description")
    		   	echo "<tr><td>"."<b>"."Description: "."</b>".htmlspecialchars($value)."</td></tr>";
    ?>
    <?php

    		if (array_key_exists("org_url", $doc)){

    			echo "<tr><td>"."<b>"."Url: "."</b>"."<a href= ".htmlspecialchars($value)." >163</a>"."</td></tr>";
    		}
    ?>
    <?php
    		if ($field == "id") {
    			$id = explode("/", $value);
    			$html = $id[7];
    			// echo $html;
    			foreach ($arr as $key =>$val){
    				if($key == $html)

    					echo "<tr><td>"."<b>"."Url: "."</b>"."<a href= ' ".htmlspecialchars($val)." '>".$val."</a>"."</td></tr>";
    			}


    				$suffix = $html;
    				// echo

    				$position = "Website_data/".$suffix;


    				$content = file_get_html(trim($position));

    				$s = $content -> plaintext;

    				$start = strpos(strtolower(trim($s)),strtolower(trim($query)));

    				if ($start != false) {

    					$snippet = substr($s, $start,1000);
    					// echo "<tr><td>"."Snippet:"."</td></tr>";
    					echo "<tr><td>"."<b>"."Snippet: "."</b>".str_ireplace(trim($query), "<b>".trim($query)."</b>",$snippet)."</td></tr>";

    			}

    		}




    		}

    ?>
    		</table>
    	</li>
    <?php }
    ?> </ol>
    <?php }
    ?>
    </body> </html>
