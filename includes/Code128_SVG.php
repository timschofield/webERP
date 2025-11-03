<?php
    //+32=ASCII Total: 106.                   
    $Code128=array('212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213', '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132', '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211', '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313', '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331', '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111', '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214', '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111', '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141', '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141', '114131', '311141', '411131', '211412', '211214', '211232', '2331112');
    
    //Congugure
    $unit='px';//Unit
    $bw=3;//bar width
    $height=50*$bw;
    $fs=8*$bw;//Font size
    $yt=45*$bw;
    $dx=2*$bw;
    $x=5*$bw;
    $y=2.5*$bw;
    $bl=35*$bw;
    
    function checksum($str){
        $cstr=str_split($str);
        $count=count($cstr);
        $sum=ord($cstr[0])-32;
        for ($i=0; $i<$count;$i++){
            $sum+=(ord($cstr[$i])-32)*$i;
        }
        $sum=$sum % 103;
        $sum+=32;
        return chr($sum);
    }
    
    function draw($Text,$type='B',$check=false){
        global $unit,$x,$Code128,$height,$bw,$fs,$dx,$yt;
        $type=preg_replace('/\W/','',$type);
        $type=substr($type,0,1);
        $type=strtoupper($type);
        $clong=(strlen($Text)+4)*11;
        $width=$bw*$clong;
        switch($type){
            case'A':
            $start=$Code128[103];
            break;
            case'B':
            $start=$Code128[104];
            break;
            case'C':
            $start=$Code128[105];
            break;
            default:
            $start=$Code128[104];
            break;
            
        }
        $ctext=$start.$Text;
        if ($check) {
        $Text.=checksum($ctext);
        }
        $Text=str_split($Text);
        $img='@';
        $img.= "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"no\"?>\n<!DOCTYPE svg PUBLIC \"-//W3C//DTD SVG 1.1//EN\" \"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd\">\n";
        $img.= "<svg width='$width$unit' height='$height$unit' version='1.1' xmlns='http://www.w3.org/2000/svg'>\n";
        //Draw Guard bar.
        $img.= "<desc>First Guard</desc>\n";
        //StartCode
        $img.=drawchar($start);
        
        //Begin Content
        foreach($Text as $char){
            $index=ord($char)-32;
            $xt=$x+$dx;
            $img.= "<desc>$char</desc>\n";
            $img.=drawchar($Code128[$index]);
            $img.= "<text x='$xt$unit' y='$yt$unit' font-family='Arial' font-size='$fs'>$char</text>\n";
        }
        //End guard bar.
        $img.=drawchar($Code128[106]);
        $img.='</svg>';
        return $img;
    }
    function drawchar($char){
        global $unit,$x,$y,$bl,$height,$bw,$fs,$bw;
        $val=str_split($char);
        $img='';
        $j=0;
        foreach($val as $bar){
            $num=(int)$bar;
            $w=$bw*$num;
            if (!($j % 2)){
                $img.= "<rect x='$x$unit' y='$y$unit' width='$w$unit' height='$bl$unit' fill='black' stroke-width='0' />\n";
            }
            $x += $w;
            $j++;
        }
        return $img;
    }
?>
