<!DOCTYPE html>
<html>

<head>
    <title>Web Crawler</title>
</head>

<body>
    <div id="content" style="margin-top:10px;height:100%;">
        <center>
            <h1>Terms and Conditions Extractor</h1>
        </center>
        <form action="index.php" method="POST">
            URL : <input name="url" size="35" placeholder="keep the -> http://" />
            <input type="submit" name="submit" value="Start Crawling" />
        </form>
        <br/>
        <?php
        
        error_reporting(0);
   include("simple_html_dom.php");
   $crawled_urls=array();
   $found_urls=array();
        
        
        
              
   if(isset($_POST['submit'])){
    $url=$_POST['url'];
    if($url==''){
     echo "<h2>A valid URL please.</h2>";
    }else{
        $parse = parse_url($url);
        $web_name = $parse['host'];
     echo "<h2>Result - URL's Found FOR (".$web_name.") </h2><ul style='word-wrap: break-word;width: 400px;line-height: 25px;'>";
     crawl_site($url);
     echo "</ul>";
    }
   }
 
function get_title($url){
  $str = file_get_contents($url);
  if((strlen($str)>0)&&strpos($url,"terms")||strpos($url,"policy")||strpos($url,"policies")||strpos($url,"legal")||strpos($url,"conditions")||strpos($url,"privacy")){
      
    $str = trim(preg_replace('/\s+/', ' ', $str)); // supports line breaks inside <title>
    preg_match("/\<title\>(.*)\<\/title\>/i",$str,$title); // ignore case
      if($title[1]!=null){
    return $title[1];          
  }else{
$end = array_slice(explode('/', rtrim($url, '/')), -1)[0];
          $end = preg_replace('/[^\p{L}\p{N}\s]/u', '', $end);
          
          return $end;
}
  }

}
        
        function print_page($url){
$d = new DOMDocument;
$mock = new DOMDocument;
$d->loadHTML(file_get_contents($url));
$body = $d->getElementsByTagName('body')->item(0);
foreach ($body->childNodes as $child){
    $mock->appendChild($mock->importNode($child, true));
}

            $data = $mock->saveHTML();
            //removes all javascript tahs
            $data = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $data);
            //removes all html tags
            $data = preg_replace('/<[^>]*>/', '', $data);
            
return $data;

        }
        
   function rel2abs($rel, $base){
    if (parse_url($rel, PHP_URL_SCHEME) != '') return $rel;
    if ($rel[0]=='#' || $rel[0]=='?') return $base.$rel;
    extract(parse_url($base));
    $path = preg_replace('#/[^/]*$#', '', $path);
    if ($rel[0] == '/') $path = '';
    $abs = "$host$path/$rel";
    $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
    for($n=1; $n>0;$abs=preg_replace($re,'/', $abs,-1,$n)){}
    $abs=str_replace("../","",$abs);
    return $scheme.'://'.$abs;
   }
        
   function perfect_url($u,$b){
    $bp=parse_url($b);
    if(($bp['path']!="/" && $bp['path']!="") || $bp['path']==''){
     if($bp['scheme']==""){$scheme="http";}else{$scheme=$bp['scheme'];}
     $b=$scheme."://".$bp['host']."/";
    }
    if(substr($u,0,2)=="//"){
     $u="http:".$u;
    }
    if(substr($u,0,4)!="http"){
     $u=rel2abs($u,$b);
    }
    return $u;
   }
        
    function get_domain($url)
{
  $pieces = parse_url($url);
  $domain = isset($pieces['host']) ? $pieces['host'] : '';
  if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
    return $regs['domain'];
  }
  return false;
}
        
        
    function write_file($url){

        if (!file_exists(get_domain($url))) {
    mkdir(get_domain($url), 0777, true);
}
        $file = get_domain($url)."/".get_title($url).".txt";
        echo "saving to... ".$file;
        echo "<br>";
        $fp = fopen($file, 'w');
        
        fwrite($fp, print_page($url));
        fclose($fp);
    }
        
   function crawl_site($u){
    global $crawled_urls;
    $uen=urlencode($u);
    if((array_key_exists($uen,$crawled_urls)==0 || $crawled_urls[$uen] < date("YmdHis",strtotime('-25 seconds', time())))){
     $html = file_get_html($u);
        
     $crawled_urls[$uen]=date("YmdHis");
     foreach($html->find("a") as $li){
      $url=perfect_url($li->href,$u);
         
         
if(strpos($url,"terms")||strpos($url,"policy")||strpos($url,"policies")||strpos($url,"legal")||strpos($url,"conditions")||strpos($url,"privacy")){
      $enurl=urlencode($url);
      if($url!='' && substr($url,0,4)!="mail" && substr($url,0,4)!="java" && array_key_exists($enurl,$found_urls)==0){
       $found_urls[$enurl]=1;
       echo "<li><strong><a target='_blank' href='".$url."'>".$url."</a></strong></li>";
//          echo print_page($url);
write_file($url);
          
      }
}else{
//echo get_title($url);
//echo "<li><strong><a target='_blank' href='".$url."'>".$url."</a></strong></li>";
    
}
         
         
         

         
     }
    }
   }
      

   ?>
    </div>
    <style>
        input {
            border: none;
            padding: 8px;
        }

    </style>
</body>

</html>
