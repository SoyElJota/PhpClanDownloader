<?php

error_reporting(E_ALL);
// Array con idexado por el id de serie segun el xml de series y valor nombre de directorio local.
$idsABajar=array();
$idsABajar['TE_SCHUGGI']='Chugginton';
$idsABajar['TE_SDAVIDE']='David el Gnomo';
$idsABajar['TE_SPEPPAP']='Peppa Pig';
$idsABajar['TE_SDINOTR']='Dino Tren';
$idsABajar['TE_SCAILLO']='Caillou';

$RutaBaseVideos='/Users/miguel/Desktop/VideosClan';



$baseHost="http://www.rtve.es";
$rutaIndex=$baseHost."/infantil/components/series.xml.inc";

$xmlLista=implode("",file($rutaIndex));
$docLista=new DOMDocument();
$docLista->loadXML($xmlLista);
$xpathLista=new DOMXPath($docLista);

$nodos=$xpathLista->query('/series/serie');
for ($i=0; $i<$nodos->length; $i++) {
    if (array_key_exists($nodos->item($i)->getAttribute('id'), $idsABajar)) {
        printSerie($nodos->item($i));
        $playList=getPlayList($nodos->item($i));
        printf("\tPlaylist en: %s\n",$playList);
        bajarSerie($RutaBaseVideos,$idsABajar[$nodos->item($i)->getAttribute('id')],$baseHost.$playList);
    }
}

function bajarSerie($rutabase,$nombreSerie, $urlPlaylist) {
    $xmlLista=implode("",file($urlPlaylist));
    $docSerie=new DOMDocument();
    $docSerie->loadXML($xmlLista);
    $xpathSerie=new DOMXPath($docSerie);
    
    $nodos=$xpathSerie->query('/videos/video');
    for ($i=0; $i< $nodos->length; $i++) {
        $titulo=$nodos->item($i)->getElementsByTagName('title')->item(0)->nodeValue;
        $rutaRemota=$nodos->item($i)->getAttribute('url');
        printf("Video: %s url:%s\n",$titulo,$rutaRemota);
                                   
        $path_parts = pathinfo($rutaRemota);
        $localFilename=$nombreSerie.' - '. ucfirst(mb_strtolower($titulo,'UTF-8')).'.'.$path_parts['extension'];
        
        $localPath=$rutabase .'/'.$nombreSerie.'/'. $localFilename;
        if (!file_exists($localPath))  {
            printf("vamos a guardar %s en %s\n",$rutaRemota,$localPath);
            if (!is_dir($rutabase.'/'.$nombreSerie)) {
                echo "Creando dir para serie\n";
                mkdir($rutabase.'/'.$nombreSerie);
            }
            
            file_put_contents($localPath, file_get_contents($GLOBALS['baseHost'].$rutaRemota));
            //die();
        }
    }
    
}

function printSerie(DOMElement $serie) {
    printf("Titulo: %s\n",$serie->getElementsByTagName('title')->item(0)->nodeValue);
    printf("\tId: '%s'\n",$serie->getAttribute('id'));
}

function getPlayList(DOMElement $serie) {
    return $serie->getElementsByTagName('playlists')->item(0)->getElementsByTagName('playlist')->item(0)->getAttribute('url_xml');

}