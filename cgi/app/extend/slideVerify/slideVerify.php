<?php

namespace sdkService\extend\slideVerify;
class slideVerify{
    
    protected $img;//图片
    protected $imageWidth;//图片宽度
    protected $imageHeight;//图片高度
    protected $tmpFilePath;//用户php临时生成图片时文件保存的位置
    
    protected $stripWidth;//颜色条宽度
    
    protected $arrColor;
    protected $fontSize;
    
    protected $fontColorMap = [
        '0'=>['r'=>0,'g'=>0,'b'=>0],//0为黑色
        '1'=>['r'=>255,'g'=>255,'b'=>255],//1为白色
    ];
    protected $fontColor = ['r'=>0,'g'=>0,'b'=>0];//字体颜色
    
    //颜色map
    protected $arrColorMap = [
        'white'=>['rgb'=>'#ffffff','chinese'=>'白','len'=>0],
        'green'=>['rgb'=>'#81e44c','chinese'=>'绿','len'=>0],
        'red'=>['rgb'=>'#e44c4c','chinese'=>'红','len'=>0],
        'yellow'=>['rgb'=>'#fadc04','chinese'=>'黄','len'=>0],
        'blue'=>['rgb'=>'#4cbae4','chinese'=>'蓝','len'=>0],
        'orange'=>['rgb'=>'#9b4ce4','chinese'=>'紫','len'=>0],
        'black'=>['rgb'=>'#3d3d3d','chinese'=>'黑','len'=>0],
    ];
    //正确的颜色
    protected $trueColorName = '';
    //正确的颜色区域
    protected $trueColorArea = ["left"=>0,"right"=>0];
    
    //生成的颜色条
    protected $colorStrip = [];
    
    //要产生的色块数量
    protected $stripBlockNum = 6;
    
    //构造函数，通过传入的width设定其他条件
    public function __construct($width=1080){
            $wordColor=0;
            $width = !empty($width)?$width:1080;//默认是1080
            $width = $width<300?300:$width;
            $this->stripWidth = $width>2160?2160:$width;

            $this->stripWidth = floor($this->stripWidth);
            $this->imageWidth =  $this->stripWidth;
            $this->fontSize = 90;
            if($this->stripWidth!=1080){
                $this->fontSize = floor($this->fontSize * $this->stripWidth / 1080);
            }
            $this->imageHeight = floor($this->imageWidth * 0.1);
            $this->img=imagecreatetruecolor($this->imageWidth, $this->imageHeight);
            $color=imagecolorallocate($this->img,255,255,254);
            imagecolortransparent($this->img,$color);
            imagefill($this->img,0,0,$color);
            //设置字体颜色
            if(!empty($this->fontColorMap[$wordColor])){
                $this->fontColor = $this->fontColorMap[$wordColor];
            }
            $this->createStripArr();
    }
    
    //创建颜色块的数组
    //1.生成颜色快
    //2.选出一个正确颜色快
    //3.计算正确颜色快的坐标
    protected function createStripArr(){
        //分出6块颜色块的长度,并填充颜色
        $arrColor = [];
        $allLen=1;
        $num = $this->stripBlockNum - 1;
        $arrTmpColor = $this->arrColorMap;
        $tail = $allLen;
        for($i=1;$i<=$num;$i++){
            $k=array_rand($arrTmpColor);
            unset($arrTmpColor[$k]);
            $tmp=0.17;
            $offset=0.01;
            $kLen=mt_rand(($tmp-$offset)*100,($tmp+$offset)*100);
            $arrColor[$k]=$kLen/100;
            $tail-=($kLen/100);
        }

        $k=array_rand($arrTmpColor);
        $arrColor[$k]=$tail;
        //随机取一个正确的颜色快
        $this->trueColorName = array_rand($arrColor);
        //计算正确颜色的坐标
        $tmpLen=0;
        foreach($arrColor as $k=>$v){
            $this->colorStrip[$k]=$v;
            if($k==$this->trueColorName){
                $this->trueColorArea['left']=$tmpLen;
                $this->trueColorArea['right']=$tmpLen+$v;
            }
            $tmpLen+=$v;
        }
    }
    
    //返回base64后的图片【弃用】
    protected function getWordPicByBase64(){
        $str="请滑动至".$this->arrColorMap[$this->trueColorName]["chinese"]."游标位置";
        $ttf=mt_rand(1,6);//有6种字体 
        $fontfile = dirname(__FILE__)."/ttfs/{$ttf}.ttf";
        $black=imagecolorallocate($this->img, $this->fontColor['r'], $this->fontColor['g'], $this->fontColor['b']);
        imagettftext($this->img,$this->fontSize,0,0,$this->fontSize,$black,$fontfile,$str);
        //每小时一个图片文件夹防止太多图片
        $picDir = "/tmp";
        if(!is_dir($picDir)){
            mkdir($picDir,777,1);
        }
        $picName = 'slideVerify.png';
        $picPath = $picDir."/".$picName;
        imagepng($this->img,$picPath);
        $imgStream = file_get_contents($picPath);
        return base64_encode($imgStream);
    }

    protected function getWordPicHex(){
        $str="请滑动至".$this->arrColorMap[$this->trueColorName]["chinese"]."游标位置";
        $ttf=mt_rand(1,6);//有6种字体
        $fontfile = dirname(__FILE__)."/ttfs/{$ttf}.ttf";
        $black=imagecolorallocate($this->img, $this->fontColor['r'], $this->fontColor['g'], $this->fontColor['b']);
        imagettftext($this->img,$this->fontSize,0,0,$this->fontSize,$black,$fontfile,$str);
        ob_start();
        imagepng($this->img);
        $stream=ob_get_contents();
        ob_clean();
        return $stream;
    }
    
    //转换颜色
    protected function switchArrColor(){
        $returnArr=[];
        foreach($this->colorStrip as $k=>$v){
            $returnArr[$this->arrColorMap[$k]['rgb']]=$v;
        }
        return $returnArr;
    }
    
    //获取实际的颜色块坐标
    public function getRealStrip(){
        $return = [
            "colorStrip"=>$this->switchArrColor(),//颜色条
            //"wordPic"=>$this->getWordPicByBase64(),//文字图片
            "wordPic"=>$this->getWordPicHex(),//获取文字图片的二进制数据
            "trueColorArea"=>$this->trueColorArea//正确颜色的区域
        ];
        return $return;
    }
}
