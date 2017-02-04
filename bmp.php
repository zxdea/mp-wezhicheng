<?php
function imagecreatefrombmp($file)
{
        global  $CurrentBit, $echoMode;
        $f=fopen($file,"r");
        $Header=fread($f,2);

        if($Header=="BM")
        {
                $Size=freaddword($f);
                $Reserved1=freadword($f);
                $Reserved2=freadword($f);
                $FirstByteOfImage=freaddword($f);

                $SizeBITMAPINFOHEADER=freaddword($f);
                $Width=freaddword($f);
                $Height=freaddword($f);
                $biPlanes=freadword($f);
                $biBitCount=freadword($f);
                $RLECompression=freaddword($f);
                $WidthxHeight=freaddword($f);
                $biXPelsPerMeter=freaddword($f);
                $biYPelsPerMeter=freaddword($f);
                $NumberOfPalettesUsed=freaddword($f);
                $NumberOfImportantColors=freaddword($f);

                if($biBitCount<24)
                {
                        $img=imagecreate($Width,$Height);
                        $Colors=pow(2,$biBitCount);
                        for($p=0;$p<$Colors;$p++)
                        {
                                $B=freadbyte($f);
                                $G=freadbyte($f);
                                $R=freadbyte($f);
                                $Reserved=freadbyte($f);
                                $Palette[]=imagecolorallocate($img,$R,$G,$B);
                        }




                        if($RLECompression==0)
                        {
                                $Zbytek=(4-ceil(($Width/(8/$biBitCount)))%4)%4;

                                for($y=$Height-1;$y>=0;$y--)
                                {
                                        $CurrentBit=0;
                                        for($x=0;$x<$Width;$x++)
                                        {
                                                $C=freadbits($f,$biBitCount);
                                                imagesetpixel($img,$x,$y,$Palette[$C]);
                                        }
                                        if($CurrentBit!=0) {freadbyte($f);}
                                        for($g=0;$g<$Zbytek;$g++)
                                        freadbyte($f);
                                }

                        }
                }


                if($RLECompression==1) //$BI_RLE8
                {
                        $y=$Height;

                        $pocetb=0;

                        while(true)
                        {
                                $y--;
                                $prefix=freadbyte($f);
                                $suffix=freadbyte($f);
                                $pocetb+=2;

                                $echoit=false;

                                if($echoit)echo "Prefix: $prefix Suffix: $suffix<BR>";
                                if(($prefix==0)and($suffix==1)) break;
                                if(feof($f)) break;

                                while(!(($prefix==0)and($suffix==0)))
                                {
                                        if($prefix==0)
                                        {
                                                $pocet=$suffix;
                                                $Data.=fread($f,$pocet);
                                                $pocetb+=$pocet;
                                                if($pocetb%2==1) {freadbyte($f); $pocetb++;}
                                        }
                                        if($prefix>0)
                                        {
                                                $pocet=$prefix;
                                                for($r=0;$r<$pocet;$r++)
                                                $Data.=chr($suffix);
                                        }
                                        $prefix=freadbyte($f);
                                        $suffix=freadbyte($f);
                                        $pocetb+=2;
                                        if($echoit) echo "Prefix: $prefix Suffix: $suffix<BR>";
                                }

                                for($x=0;$x<strlen($Data);$x++)
                                {
                                        imagesetpixel($img,$x,$y,$Palette[ord($Data[$x])]);
                                }
                                $Data="";

                        }

                }


                if($RLECompression==2) //$BI_RLE4
                {
                        $y=$Height;
                        $pocetb=0;

                        /*while(!feof($f))
                        echo freadbyte($f)."_".freadbyte($f)."<BR>";*/
                        while(true)
                        {
                                //break;
                                $y--;
                                $prefix=freadbyte($f);
                                $suffix=freadbyte($f);
                                $pocetb+=2;

                                $echoit=false;

                                if($echoit)echo "Prefix: $prefix Suffix: $suffix<BR>";
                                if(($prefix==0)and($suffix==1)) break;
                                if(feof($f)) break;

                                while(!(($prefix==0)and($suffix==0)))
                                {
                                        if($prefix==0)
                                        {
                                                $pocet=$suffix;

                                                $CurrentBit=0;
                                                for($h=0;$h<$pocet;$h++)
                                                $Data.=chr(freadbits($f,4));
                                                if($CurrentBit!=0) freadbits($f,4);
                                                $pocetb+=ceil(($pocet/2));
                                                if($pocetb%2==1) {freadbyte($f); $pocetb++;}
                                        }
                                        if($prefix>0)
                                        {
                                                $pocet=$prefix;
                                                $i=0;
                                                for($r=0;$r<$pocet;$r++)
                                                {
                                                        if($i%2==0)
                                                        {
                                                                $Data.=chr($suffix%16);
                                                        }
                                                        else
                                                        {
                                                                $Data.=chr(floor($suffix/16));
                                                        }
                                                        $i++;
                                                }
                                        }
                                        $prefix=freadbyte($f);
                                        $suffix=freadbyte($f);
                                        $pocetb+=2;
                                        if($echoit) echo "Prefix: $prefix Suffix: $suffix<BR>";
                                }

                                for($x=0;$x<strlen($Data);$x++)
                                {
                                        imagesetpixel($img,$x,$y,$Palette[ord($Data[$x])]);
                                }
                                $Data="";

                        }

                }


                if($biBitCount==24)
                {
                        $img=imagecreatetruecolor($Width,$Height);
                        $Zbytek=$Width%4;

                        for($y=$Height-1;$y>=0;$y--)
                        {
                                for($x=0;$x<$Width;$x++)
                                {
                                        $B=freadbyte($f);
                                        $G=freadbyte($f);
                                        $R=freadbyte($f);
                                        $color=imagecolorexact($img,$R,$G,$B);
                                        if($color==-1) $color=imagecolorallocate($img,$R,$G,$B);
                                        imagesetpixel($img,$x,$y,$color);
                                }
                                for($z=0;$z<$Zbytek;$z++)
                                freadbyte($f);
                        }
                }
                return $img;

        }


        fclose($f);


}

function freadbyte($f)
{
        return ord(fread($f,1));
}

function freadword($f)
{
        $b1=freadbyte($f);
        $b2=freadbyte($f);
        return $b2*256+$b1;
}

function freaddword($f)
{
        $b1=freadword($f);
        $b2=freadword($f);
        return $b2*65536+$b1;
}

?>
