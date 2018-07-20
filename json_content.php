<?php header('Access-Control-Allow-Origin: *'); ?>
<?php
      if(isset($_GET["query"])){
        
            $query = $_GET["query"];
            $arry = explode(" ", $query);
            $count = count($arry);
            $html = "http://localhost:8983/solr/core/suggest?indent=on&q=".trim($arry[$count - 1])."&wt=json";
            $data="";
            $curl= curl_init();
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            curl_setopt($curl, CURLOPT_URL, $html);

            $data = curl_exec($curl);
            curl_close($curl);

            echo $data;
          }

?>
