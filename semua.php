<?php
require_once("conn.php");
function query($sql){
	global $conn;
	$query = $conn->query($sql);
	//looping untuk mengambil setiap baris data
	while($hasil[] = $query->fetch_object());
	array_pop($hasil);
	return $hasil;
}
function ambilRandUser(){
	$sql="SELECT * FROM users ORDER BY RAND() LIMIT 1";
	$data = query($sql);
	return $data;
}
function ambilRandAnime(){
$sql="SELECT * FROM animes ORDER BY RAND() LIMIT 1";
	$data = query($sql);
	return $data;
}
$nUser = 5;
$nItem = 4;
$F = [[0,4,0,2],[0,5,4,0],[0,2,2,0],[2,0,3,3],[0,4,0,2]];
$k=2;
$T=3;
$d=2;
$alpha = 1;
$B=1;
$Y = 1;
$N = 0.01;
$n=0.1;
$W = [];
function randomData($x,$y){
	$all=[];
	for($i=0;$i<$x;$i++){
		$temp = [];
		for($j=0;$j<$y;$j++){
			$temp[] = rand(0, 10) / 10;;
		}
		$all[] = $temp;
	}
	return $all;
}
function randomVektor($n){
	$temp=[];
	for($i=0;$i<$n;$i++){
		$temp[] = rand(0,10)/10;
	}
	return $temp;
}
$W = randomData($nUser,$d);
$V = randomData($d,$nItem);
$b = randomVektor($nItem);
// echo "<pre>";
// print_r($W);
// echo "</pre>";
// echo "<pre>";
// print_r($V);
// echo "</pre>";
// echo "<pre>";
// print_r($b);
// echo "</pre>";
$nSimilarUser=[];
function findSimilar($a,$b){
	global $F;
	$atas = 0;
	$bawah=0;
	for($i=0;$i<count($F[$a]);$i++){
		$atas += ($F[$a][$i]*$F[$b][$i]);
	}
	$bawah = findBawahSimilar($a)*findBawahSimilar($b);
	return $atas/$bawah;
}

function findBawahSimilar($u){
	global $F;
	$total = 0;
	for($i=0;$i<count($F[$u]);$i++){
		$total += pow($F[$u][$i],2);
	}
	return sqrt($total);
}
$similarUser=[];
for($i=0;$i<$nUser;$i++){
	$temp=[];
	for($j=0;$j<$nUser;$j++){
		if($i!=$j){
			$temp[] = findSimilar($i,$j);
		}else{
			$temp[]= 0;
		}
	}
	$similarUser[]= $temp;
}

function findTetangga($id){
	global $similarUser;
	$tetangga = [];
	$dia = $similarUser[$id];
	$nBesar = $dia[$id];
	asort($dia);
	$i=0;
	foreach($dia as $key => $value){
		if($i>count($dia)-2){
			$tetangga[] = $key;
		}
		else if($i==count($dia)-2){
			$temp = array_search($dia[$key],$dia);
			if($temp<$key)
				$tetangga[] = $temp;
			else
				$tetangga[] = $key;
		}
		$i++;
	}
	return $tetangga;
}
$arrP=[];
$arrC=[];
$arrL=[];
function findP($id){
	global $F;
	$temp = [];
	for($i=0;$i<count($F[$id]);$i++){
		if($F[$id][$i]>0){
			$temp[] = $i;
		}
	}
	return $temp;
}
function findC($id){
	global $F;
	$tetangga = findTetangga($id);
	$tempA = findP($tetangga[0]);
	$tempB = findP($tetangga[1]);
	$temp = findP($id);
	$result = array_diff($tempA,$temp);
	$temp=array_merge($temp,$result);
	$result2 = array_diff($tempB,$temp);
	$result=array_merge($result,$result2);
	return $result;
}
function findL($id){
	global $F,$nItem;
	$result=[];
	$temp  = array_merge(findP($id),findC($id));
	for($i=0;$i<$nItem;$i++){
		$result[] = $i;
	}
	$result = array_diff($result,$temp);
	return $result;
}
echo "<pre>";
print_r($similarUser);
// echo "</pre><h1>Lain</h1><pre>";
// for($i=0;$i<$nUser;$i++){
// 	echo "Tetangga<pre>";
// 	print_r(findTetangga($i));
// 	echo "Find P </pre><br><pre>";
// 	print_r(findP($i));
// 	echo "Find C</pre><br><pre>";
// 	print_r(findC($i));
// 	echo "Find L</pre><br><pre>";
// 	print_r(findL($i));
// 	echo "</pre>";
// }
// tahap training
$userAcak = 1;
$itemAcakP = findP($userAcak)[0];
$itemAcakC=findC($userAcak)[0];
$itemAcakL=findL($userAcak)[0];
// $tetangga = findTetangga($userAcak);
function findSui($user,$itemAcakP,$similarUser){
	$total=0;
	$tetangga = findTetangga($user);
	for($i=0;$i<count($tetangga);$i++){
		$nilai=in_array($itemAcakP,findP($tetangga[$i]))?1:0;
		// echo $nilai."<br>";
		// echo $similarUser[$user][$tetangga[$i]];
		$total += $similarUser[$user][$tetangga[$i]]*$nilai;
	}
	return $total;
}
function findSut($user,$itemAcakC,$similarUser){
	$total=0;
	$tetangga = findTetangga($user);
	for($i=0;$i<count($tetangga);$i++){
		$nilai=in_array($itemAcakC,findP($tetangga[$i]))?1:0;
		$total += $similarUser[$user][$tetangga[$i]]*$nilai;
	}
	return $total;
}
function findCuit($Sui,$Sut){
	return (1+$Sui)/(1+$Sut);
}
function findRui($d,$u,$W,$Vt,$B,$itemAcakP){
	$total=0;
	for($i=0;$i<$d;$i++){
		$total += ($W[$u][$i]*$Vt[$itemAcakP][$i]);
	}
	return $total+$B[$itemAcakP];
}
function findRut($d,$u,$W,$Vt,$B,$itemAcakC){
	$total=0;
	for($i=0;$i<$d;$i++){
		$total += ($W[$u][$i]*$Vt[$itemAcakC][$i]);
	}
	return $total+$B[$itemAcakC];
}
function findRuj($d,$u,$W,$Vt,$B,$itemAcakL){
	$total=0;
	for($i=0;$i<$d;$i++){
		$total += ($W[$u][$i]*$Vt[$itemAcakL][$i]);
	}
	return $total+$B[$itemAcakL];
}
$W = [[0.3,0.5],[0.6,0.2],[0.1,1],[0.3,0.1],[0.5,0.4]];
$V = [[0.2,0.4,0.3,0.4],[0.6,0.3,0.5,0]];
$Vt = [[0.2,0.6],[0.4,0.3],[0.3,0.5],[0.4,0]];
$B = [1,0.4,0.1,0.2];
$Sui = findSui($userAcak,$itemAcakP,$similarUser);
$Sut = findSut($userAcak,$itemAcakC,$similarUser);
$Cuit = findCuit($Sui,$Sut);
$Cutj = 1+$Sut;
$Cuij = 1 + $Sui;
$Rui =findRui($d,$userAcak,$W,$Vt,$B,$itemAcakP);
$Rut=findRut($d,$userAcak,$W,$Vt,$B,$itemAcakC);
$Ruj=findRuj($d,$userAcak,$W,$Vt,$B,$itemAcakL);
$Ruit = $Cuit*($Rui-$Rut);
$Rutj = $Cutj*($Rut-$Ruj);
$Ruij = $Cuij*($Rui-$Ruj);
echo $Sui."<br>";
echo $Sut."<br>";
echo $Cuit." ".$Cutj." ".$Cuij." ".$Rui." ".$Rut." ".$Ruj;
echo "<br>".$Ruit." ".$Rutj." ".$Ruij;
// Perolehan nilai gradien untuk Matriks
$W1=[];
for($i=0;$i<$d;$i++){
	$W1[] = (((1*$Cuit)/(1+pow(exp(1),$Ruit)))*($Vt[$itemAcakP][$i]-$Vt[$itemAcakC][$i])) + (((1*$Cutj)/(1+pow(exp(1),$Rutj)))*($Vt[$itemAcakC][$i]-$Vt[$itemAcakL][$i])) + (((1*$Cuij)/(1+pow(exp(1),$Ruij)))*($Vt[$itemAcakP][$i]-$Vt[$itemAcakL][$i])) - (0.01*$W[$userAcak][$i]);
}
print_r($W1);
// Perolehan nilai gradien untuk matriks  untuk V.
$V1=[];
$V2=[];
$V3=[];
for($i=0;$i<$d;$i++){
	$V1[] = (((1*$Cuit)/(1+pow(exp(1),$Ruit)))*($W[$userAcak][$i]))  + (((1*$Cuij)/(1+pow(exp(1),$Ruij)))*($W[$userAcak][$i])) - (0.01*$Vt[$itemAcakP][$i]);
}
for($i=0;$i<$d;$i++){
	$V2[] = (((1*$Cuit)/(1+pow(exp(1),$Ruit)))*(-1*$W[$userAcak][$i]))  + (((1*$Cutj)/(1+pow(exp(1),$Rutj)))*($W[$userAcak][$i])) - (0.01*$Vt[$itemAcakC][$i]);
}
for($i=0;$i<$d;$i++){
	$V3[] = (((1*$Cutj)/(1+pow(exp(1),$Rutj)))*(-1*$W[$userAcak][$i]))  + (((1*$Cuij)/(1+pow(exp(1),$Ruij)))*(-1*$W[$userAcak][$i])) - (0.01*$Vt[$itemAcakL][$i]);
}
print_r($V1);
print_r($V2);
print_r($V3);
//mencari b
// $W1[] = (((1*$Cuit)/(1+pow(exp(1),$Ruit)))*($Vt[$itemAcakP][1]-$Vt[$itemAcakC][1])) + (((1*$Cutj)/(1+pow(exp(1),$Rutj)))*($Vt[$itemAcakC][1]-$Vt[$itemAcakL][1])) + (((1*$Cuij)/(1+pow(exp(1),$Ruij)))*($Vt[$itemAcakP][1]-$Vt[$itemAcakL][1])) - (0.01*$W[$userAcak][1]);
// echo round($Sui,3);
// $Sut=0;
// $Cuit=0;
// $Cuij=0;
// $Rui=0;
// $Rut=0;
// $Ruj=0;
// $Ruit=0;
// $Ruij=0;

?>