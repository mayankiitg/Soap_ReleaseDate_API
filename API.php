<?php
//find ep name date for following
 
$url = $_SERVER['REQUEST_URI'];
$path = parse_url($url, PHP_URL_PATH);

$pathComponents = explode("/", trim($path, "/")); // trim to prevent

                                              // empty array elements
if(strcmp($pathComponents[1], "soap") === 0) // prints 'abc'
{
    //echo count($pathComponents);
    $name=$pathComponents[2];
    $season = $pathComponents[3];
    $episode= $pathComponents[4];

}

else
{
    echo "not soap";
}

////////////////////////////////


include('simple_html_dom.php');
$html = file_get_html($name . '.html');
$cmd='curl --proxy http://username:password@proxy:port "www.epguides.com/' . $name . '"';
//$html=shell_exec($cmd); 
foreach($html->find('div#eplist') as $e)
{
    $data=$e->innertext . '<br>';
    $search=(string)$season . '-' . (string)$episode;
    //$pos is position of 2-6
    $pos = strpos($data, $search);
     
    if ($pos===false)
    {
        echo "Whoa! cool down.";
        break;
    }
    //echo $data;
    $pos1=$pos;
    $pos2=$pos;
    while($data[$pos1]!=='.')
    {
        $pos1--;
    }
     
    $pos2=$pos1;
    while($data[$pos2]!==' ')
    {
        $pos2--;
    }
    $pos2++;
    //pos2 is start pos of sno
    $sno=intval(substr($data, $pos2, $pos1-$pos2+1));
    $nextsno=$sno+1;
    $nextsnostr = strval($nextsno) . '.';
    //find pos of the next serial no
    $pos1 = strpos($data, $nextsnostr);
    //echo $data[$pos] . '<br>';
    //echo $search;
    //extract single string
    $str = substr($data, $pos, $pos1-$pos);
    $pos=strpos($str, $search);
 
    while($str[$pos]!==' ')
        $pos++;
    $pos3=strpos($str, '<a');
    $pos3--;
    $date=substr($str, $pos, $pos3-$pos);
    $date=ltrim(rtrim($date));
    
    $pos1=strpos($str, '>');
    $pos1++;
    $pos2=strpos($str, '</a');
    $epname=substr($str, $pos1, $pos2-$po1+1);
    $epname=trim($epname);
    $epname=substr($epname,0,strlen($epname)-4);

    //echo '<br>'.$date.'<br>'.$epname;
    
     
}

///// convert json


$arr_for_json  = array('Soap name' => $name, 'Season' => $season, 'episode' => $episode ,'Date' => $date , 'Episode Name' => $epname );
echo json_encode($arr_for_json);

//    

?>
