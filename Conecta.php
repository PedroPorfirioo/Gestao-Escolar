<?php
$host="localhost";
$usuario="root";
$senha="";
$nomedobanco="gestaoescolar";

$conexao=mysqli_connect($host,$usuario,$senha,$nomedobanco);

if($conexao){
    echo "";
}else{
    echo "Falhou.";
}
?>