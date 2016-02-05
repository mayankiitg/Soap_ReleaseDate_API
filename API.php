<?php
//find ep name date for following
 
$url = $_SERVER['REQUEST_URI'];
$path = parse_url($url, PHP_URL_PATH);

$pathComponents = explode("/", trim($path, "/")); // trim to prevent

                                             
/*
*   url of the form temp.php/Suits/5/12
*   or temp.php/today

*/


//echo count($pathComponents);
$name=$pathComponents[1];
$season = $pathComponents[2];
$episode= $pathComponents[3];


////////////////////////////////
//if url of the form soap/today then display all episodes releasing today
include('simple_html_dom.php');


//if arguement is today, run the script to cache the output
if ($argv[1]==="today")
{
    date_default_timezone_set('Asia/Kolkata');
       

    $date= date('d-m-Y',strtotime("-1 days"));
    $timestamp = strtotime($date);

    $day = date('l', $timestamp);
    

    $dateObj   = DateTime::createFromFormat('!m', intval(substr($date, 3, 2)));
    $monthName = $dateObj->format('F'); // March

    $date = substr($date, 0, 2) . ' ' . substr($monthName, 0, 3) . ' ' . substr($date, 8, 2);
    
    echo $day . ' ' . $date . "\n";



    $cmd='curl --proxy http://127.0.0.1:3128 "http://epguides.com/grid/"';
    $html1=shell_exec($cmd); 
    $html = str_get_html($html1);
    $search = "epguides.com - " . $day. " US TV Schedule";
    $pos = strpos($html, $search);
    $str=substr($html, $pos-1200);
    //find a tags between pos1 and pos2
    $pos1 = strpos($str, "<table");
    $str = substr($str, $pos1);
    $pos2 = strpos($str, "</table>");
    $str = substr($str, 0, $pos2+8);
    //$str now contains the table containing all shows on the day
    foreach (str_get_html($str)->find('a') as $e)
    {
        $url='http://epguides.com';
        $temp=$e->href;
        if(substr($temp, 0, 2)==="..")
        {
            $epname=substr($temp, 3);
            $url = $url . "/" . $epname;
            $epname=rtrim($epname, "/");
            $cmd='curl --proxy http://127.0.0.1:3128 "' . $url . '"';
            $html2=shell_exec($cmd); 
            $html3 = str_get_html($html2);

            $pos=strpos(htmlentities($html3), $date);
            if($pos===false)
                echo $epname . "-->no new episodes" . "\n";
            else
            {
                echo $epname;
                $pos--;
                
                while(htmlentities($html3)[$pos]==' ')
                    $pos--;
                
                $pos2=$pos;
                while(htmlentities($html3)[$pos]!==' ')
                    $pos--;
                $pos1=$pos++;
                $str1=substr(htmlentities($html3), $pos1, $pos2-$pos1+1);
                $se=explode('-', $str1);
                echo "-->Season " . $se[0] . " Episode " . $se[1] . "\n";
            }

        }
    }

    exit("");
}

//if today in url, redirect to cached txt file
if($name=="today")
    header("Location: ../../downloadToday.txt");

//////////////////////////////////////////
//here we fetch details of a specific episode
else
{
    //$html = file_get_html($name . '.html');
    //$cmd='curl --proxy 172.16.114.173:3128 "www.epguides.com/' . $name . '"';
    $cmd='curl --proxy http://127.0.0.1:3128 "www.epguides.com/' . $name . '/"';
    $html1=shell_exec($cmd); 
    $html = str_get_html($html1);
    //check if the series name is correct
    $search = "The page cannot be found";
    if(strpos($html, $search)===false)
         exit("Whoa! something wrong there mate.");

    // echo $html;
    //$e1=$html->find('div#eplist',0);

    //echo gettype($html);

    foreach($html->find('div#eplist') as $e)
    {
       $data=$e->innertext . '<br>';
        $search=(string)$season . '-' . (string)$episode;
        //$pos is position of 2-6
        $pos = strpos($data, $search);
         
        if ($pos===false)
        {
            exit("Whoa! something wrong there mate.");
            // echo ;
            // break;
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
       
         // echo "sd";
    }

    ///// convert json
    if($name!=="today")
    {
        $arr_for_json  = array('Soap name' => $name, 'Season' => $season, 'episode' => $episode ,'Date' => $date , 'Episode Name' => $epname );
        echo json_encode($arr_for_json);
    }
    //    
}
?>
