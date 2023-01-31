<?php
	global $gtipobd1,$gdsn1,$guser1,$gpass1,$gtipobd3,$gdsn3,$gtipobd5,$guser3,$gpass3,$gtipobd4,$gdsn4,$guser4,$gpass4,$gdsn5,$guser5,$gpass5,$gdsn7,$guser7,$gpass7,$params,$texto,$salidaticket;
	require $_SESSION['ruta3']."datos/idiomas/ialtacur.PHP";
	
$descamp='';
	
	$micodpers2=explode('|',$micodpers);
	if (isset($micodpers2[1])){
		$datos=webUsuar2Escuelas($micodpers);
		$datos['clave']='tmp';
		$micodpers=compruebaExisteUsuario($datos);
		if ($micodpers==""){
			$micodpers=creaUsuarioEscuelas($datos,0,0,0,1);
			$micodpers=$micodpers['numabo'];
						
		}
	}

	
	debug::ini($micodpers.'|'.$micodcur.'|'.date('d/m/Y H:i:s'));

	$descomp='';
	$respuesta['codigo']=1;
	$respuesta['usuarcur']='';
	$respuesta['reciban']='';

	if ($micodpers==='0'){
		$respuesta['msg']='Hemos tenido un problema al generar el alta, por favor, vuelta a intentar la operación';
		$respuesta['codigo']=0;
		
		debug::saveError($args,'ALTACURSO',$respuesta['msg']);
		return $respuesta;
	}
	if ($cursob=='')
		$cursob=$micodcur;
	$controles=1;
	if (isset($_SESSION['controles']))
		if ($_SESSION['controles']==1){
			$controles=0;
		}
	
	$_SESSION['tipc']=$tipc;
	$_SESSION['oper']=$oper;
	if ($usuadd>0)
		$usuadd--;
	if ($usuadd=='')
		$usuadd=0;
	if ($checkper==0)
	$checkper=1;
	$especial['suple']='';
	$curso1dia=0;
    $con=bd::globalCon(3);
    $con2=bd::globalCon(1);
	$conb=bd::globalCon(4);
	$recibosconta=array();
	$recibosvb=array();
	$juntarecibos=$params["JuntaRecibos"];
	$recibos=array();
	$numrecfinal="";
	$partic[0]="";
	$partic[1]="";
	$obstarjeta="";
	$cadenarecibo=obsticket(1);
	$contarecibos=0;
    $tipopago=$pago;
	$modpago=$tipopago;
	$claban="";
	
	if ($pago==3){
    	$pago=1;
		$tipopago=3;
	}
	if ($tipopago==1){
		
		if ($referencia=="")
		exit;
		if ($operacion=="")
		exit;
	}
	else{
		$referencia="";
		$operacion="";
	}
	
	if ($pago==4){
		$pago=1;
		$tipopago=4;
	}
	if ($pago==5){
	    $pago=1;
		$tipopago=5;
	}
	if ($pago==8){
	    $pago=1;
		$tipopago=8;
	}
	if ($pago==10){
	    $pago=1;
		$tipopago=10;
	}
	if ($pago==9){
	    $pago=1;
		$tipopago=9;
	}
	if ($pago==11){
	    $pago=2;
		$tipopago=11;
	}
	$suplemento=calculaSuplementos($suple,$cursob,$micodpers,$catusu,$usuadd);
	$user=usuario::getInstance($micodpers);
	
   
	$domabo=$user->datos['Dom'];
	$apellidos=$user->datos['Ape'];
	$nomabo=$user->datos['Nom'];
	$cifabo=$user->datos['Dni'];
	$cpabo=$user->datos['CodPos'];
	$pobabo=$user->datos['Pob'];
	$titabo=$user->datos['Tit'];	
	$pagdom=$user->datos['PagDom'];
	$bancoabo=$user->datos['Banco'];
	$agenabo=$user->datos['Agen'];
	$respuesta['directorio']=$user->datos['AdmiteDirectorio'];
	$empadronado=$user->datos['Empadronado'];
	$numabofac=$user->datos['NumAboPagador'];
	if ($pagdom=='N' && $numabofac=='' && isset($params['PagadorCesta'])){
		$numabofac=$params['PagadorCesta'];
	}
	if ($numabofac=='')
		$numabofac=$user->datos['NumAbo'];
	
    $edadabo=adodb_date('d/m/Y',dttm2unixtime($user->datos['FecNac']));
	
    $telabo=$user->datos['Tel'];
    $mailabo=$user->datos['Email'];
	$numtar=$user->datos['NumTar'];	
	
	$datosusu['tit']=$titabo;
	$datosusu['ape']=$apellidos;
	$datosusu['nom']=$nomabo;
	$datosusu['cif']=$cifabo;
	$datosusu['ent']=$user->datos['Ent'];
	$datosusu['suc']=$user->datos['Suc'];
	$datosusu['digcon']=$user->datos['DigCon'];
	$datosusu['cue']=$user->datos['Cue'];
	$datosusu['paisIBAN']=$user->datos['PaisIBAN'];
	$datosusu['digconIBAN']=$user->datos['DigConIBAN'];
	$datosusu['pagdom']=$pagdom;
	$datosusu['numabofac']=$numabofac;	
	$especial['dencatusu']=$user->datos['Denom'];

	if ($params['TipCli']=="Etxebarri" && $_SESSION['tipc']!="B" && $_SESSION['oper']==0)
	{
		$descuento=calculaDescuentoEmte($descuento,$micodpers);
	}
	
	$sql="SELECT cursos.bononominal,cursos.tpvvirtual,cursos.codcol,cursos.act,cursos.des,cursos.tipcur,cursos.denabr,cursos.cod,ordenante,confac,cursos.complejo,curso1dia, Act,cursos.tiptic,cursos.numcredlru,codnivel,numcredects,actividad.tipemail as atipemail,actividad.tipsms as atipsms,cursos.tipemail as ctipemail,cursos.tipsms as ctipsms FROM ACTIVIDAD, CURSOS Where ACTIVIDAD.CodAct=CURSOS.Act and ltrim(CURSOS.Cod) = '".trim($micodcur)."'";
	
	$rec=bd::ejecuta($con,$sql,0,1);
	$micodcur=bd::result($rec,"cod");
	
	$bononominal=bd::result($rec,"bononominal");
	$tpvvirtual=bd::result($rec,"tpvvirtual");
	$act=bd::result($rec,"act");
	$confac=bd::result($rec,"confac");
	$tipcur=bd::result($rec,"tipcur");
	$descurso=bd::result($rec,"des");
	$codcol=bd::result($rec,"codcol");
	$datosusu=usuarBan($micodpers,$datosusu,'0',1,$act,$tipcur);
	$jugadores=explode("|",$jugadores);
	$totaljugadores=1;
	for ($x=0;$x<10;$x++){
		
		if (!isset($jugadores[$x]))
		$jugadores[$x]="";
		else{
			if ($jugadores[$x]!=""){
				$totaljugadores++;
				$sql="SELECT Ape, Nom, Dom, Dni,CodPos, Pob, Tit, paisiban,digconiban,Ent, Suc, DigCon, Cue, PagDom, Banco, Agen,Empadronado, FecNac, Tel, Email,NumTar,clatasa FROM USUAR,catusu WHERE NumAbo='".$jugadores[$x]."' and usuar.catusu=catusu.catusu";		
				$recUSU=bd::ejecuta($con,$sql);
				$datosjug[$x]['numabo']=$jugadores[$x];
				$datosjug[$x]['ape']=bd::result($recUSU,'Ape');
				$datosjug[$x]['nom']=bd::result($recUSU,'Nom');
				$datosjug[$x]['cif']=bd::result($recUSU,'Dni');
				$datosjug[$x]['ent']=bd::result($recUSU,'Ent');
				$datosjug[$x]['suc']=bd::result($recUSU,'Suc');
				$datosjug[$x]['tit']=bd::result($recUSU,'tit');
				$datosjug[$x]['digcon']=bd::result($recUSU,'DigCon');
				$datosjug[$x]['cue']=bd::result($recUSU,'Cue');
				$datosjug[$x]['paisIBAN']=bd::result($recUSU,'paisIBAN');
				$datosjug[$x]['digconIBAN']=bd::result($recUSU,'digconIBAN');
				$datosjug[$x]['pagdom']=bd::result($recUSU,'PagDom');
				$datosjug[$x]=usuarBan($jugadores[$x],$datosjug[$x],'1',1,$act,$tipcur);
				
				$cadenajug[$x]="'".$datosjug[$x]['numabo']."', '".substr($datosjug[$x]['ape'].", ".$datosjug[$x]['nom'], 0, 30)."', '".$datosjug[$x]['cif']."', '".$datosjug[$x]['tit']."', '".$datosjug[$x]['ent']."', '".$datosjug[$x]['suc']."', '".$datosjug[$x]['digcon']."', '".$datosjug[$x]['cue']."', '".$datosjug[$x]['paisIBAN']."','".$datosjug[$x]['digconIBAN']."',";
			}
		}
	}
	if ($jugadores[0]==''){
		for ($x=0;$x<$usuadd;$x++){
			$jugadores[$x]='ZZZZZZ';
		}
	}

	
	$actividad=bd::result($rec,"Act");
	$denabr=traduce2('ESCUELAS-CURSOS',trim(bd::result($rec,"Cod")),'DENABR',bd::result($rec,'DenAbr'));
	$ordenante=bd::result($rec,"ORDENANTE");
	if ($ordenante=='')
		$ordenante='000';
	$complejo=bd::result($rec,"Complejo");
	$numcred=bd::result($rec,"numcredlru");
	$codnivel=bd::result($rec,"codnivel");
	$credi2=bd::result($rec,"numcredects");
	
	$tipemail=bd::result($rec,"ctipemail");
	$tipsms=bd::result($rec,"ctipsms");
	if ($tipemail=='')
		$tipemail=bd::result($rec,"atipemail");
	if ($tipsms=='')
		$tipsms=bd::result($rec,"atipsms");		
	
	if (adaptaCon('bit',bd::result($rec,"curso1dia"),3)==true)
		$curso1dia=1;
	else
		$curso1dia=0;
	
	if ($creditos==1){
		$credi1=$numcred;
	}
	else{
		$credi1=0;
		$credi2=0;
	}	
	if ($ordenante=="")
	$ordenante="000";
	$tiptic=bd::result($rec,"tiptic");
	if ($tiptic=="")
	$tiptic="01";
	if ($_SESSION['tipc']!=="B"){
		$descuento=calculaDescuento("escuelas",$micodcur,$micodpers,'',0,'',$catusu,$codred);
	}
	else{
		$descuento=calculaDescuento("Entradas",$claveomesatasa,$micodpers,'',0,'',$catusu,$codred);
	}

	$sql="SELECT Papel,idenC57 from ORDENAN where CodOrd='".$ordenante."'";
	$rec=bd::ejecuta($con,$sql,0,1);
	$papel=bd::result($rec,"Papel");
	$ref2="";
	if ($tipopago==3){
		$ref2=bd::result($rec,"idenC57");
	}
	
	if ($papel=="")
	$papel="C57";
	
	$justificar=$descuento['jus'];
	if ($_SESSION['tipc']!=="B"){
		$sql = "SELECT justificar FROM CURSOSTAS WHERE ClaveOmesa='".$claveomesatasa."'";
		$rec=bd::ejecuta($con,$sql,0,1);
		if (bd::haydatos($rec))
			if (bd::result($rec,"justificar")==1)
				$justificar=1;
	}
	if ($pago==6 && $justificar==0)
		exit;
	if ($justificar==1){
		if ($pago==6){
			$pago=1;
			$tipopago='C';
		}
	}
	
	$textcamp='';
	if ($camp!=''){
		$sql="select * from campana where codcamp='".$camp."'";
		$rec=bd::ejecuta($con,$sql);
		$textcamp=bd::result($rec,"DesCamp");
	}
	$sql=existeUsuarCur($micodpers,$micodcur);
  	$rec=bd::ejecuta($con,$sql);
  	if ($micodpers!="ZZZZZZ")
	if (bd::haydatos($rec) && $_SESSION['tipc']!="B") {
		limpiaBloqueoCurso($session_id,1,$micodcur);
		$respuesta['codigo']=0;
		$respuesta['msg']=texto('Aviso1');
		debug::saveError($args,'ALTACURSO',$respuesta['msg']);
		return $respuesta;
		exit;
	}	

	$dentas='';
	$tiptas='G';
	if ($fecalta=='')
		$fecalta=date('d/m/Y');
	$fecalta2=$fecalta;
	if ($_SESSION['tipc']!=="B"){
		
		$sql = "SELECT Den,Cod, CatUsu,justificar,CLAVEOMESA,tiptas FROM CURSOSTAS WHERE ClaveOmesa='".$claveomesatasa."'";
	
		$rec = bd::ejecuta($con,$sql,0,1);
		$dentas = traduce2('ESCUELAS-CURSOSTAS',trim(bd::result($rec,"Claveomesa")),'DENLAR',bd::result($rec,'Den'));
		$usuadd2=$usuadd+1;
		$observaciones='***'.$dentas.' x '.$usuadd.' *** '.$observaciones;
		$clatasa = bd::result($rec,'CatUsu');
		$cursotasa = bd::result($rec,'Cod');
		$tiptas=bd::result($rec,"tiptas");
		$tasausada=$clatasa;
		$reducusada=$descuento['cod'];
		if (bd::result($rec,"justificar")==1)
			$justificar=1;
		
	}
	else{
		$contarperiodos=1;
		$forpesc=0;
		if ($empadronado==false || $empadronado=="NoE")
		$empadronado="NoE";
		else
		$empadronado="";
		if ($descuento[0]!=0)
		$reducusada=$descuento['cod'];
		else
		$reducusada='00';
		$cursocompleto="S";
		$cursotasa="";
		$sql="select * from TipEntrada where ltrim(Cod)='".trim($claveomesatasa)."'";
		$rec=bd::ejecuta($con,$sql,0,1);
		$clatasa=bd::result($rec,"CatUsu");
		$tasausada=$clatasa;
		$codiva=bd::result($rec,"codiva");
		$codtipent=bd::result($rec,"Cod");
		$destipent=traduce2('ESCUELAS-TIPENTRADA',trim(bd::result($rec,'Cod')),'DES',bd::result($rec,'Des'));
		$importe=bd::result($rec,"importe".$empadronado);
		$clacaj=bd::result($rec,"ClaCaj");
		$ordenante=bd::result($rec,"ordenante");
		$aux['total']=$importe;
		$aux['total2']=$importe;
		$aux['des']=$descuento['cod'];
		$aux['abr']=$destipent;
		$aux['can']=1;
		$aux['cod']=$codtipent;		
		$aux['clacaj']=$clacaj;				
		$aux['codiva']=$codiva;	
		
		
		$cursototal=$importe;
		
		$clacajcur=$clacaj;
		$clacajtar="";
		$clacajmat="";
		if ($descuento[0]!=0){
			$importe=$importe+($importe*$descuento[0])/100;
		}
		if ($descuento[1]!=0){
			$importe=$importe+$descuento[1];
		}
		
		if ($importe<0)
		$importe=0;
		$aux['total']=$importe;
		$aux['total2']=$importe;
		$entradas2[]=$aux;
		if ($tiptas!='G')
			$importe=$importe+$importe*$usuadd;
		
	}
	
	if ($seguro!="00"){
		$sql = "SELECT Cod, CatUsu FROM CURSOSTAS WHERE ClaveOmesa='".$claveomesatasas."'";
		
		$rec = bd::ejecuta($con,$sql,0,1);
		$clatasas = bd::result($rec,'CatUsu');
		$cursotasas = bd::result($rec,'Cod');
		$sql="select codcur from seguros where Cod='".$seguro."'";
		$rec=bd::ejecuta($con,$sql);
		$micodseguro=bd::result($rec,"codcur");
	}

	for ($bucle=0;$bucle<2;$bucle++){
		
		if ($bucle==0){
			$micodcur=$micodcur;
			$cursotasa=$cursotasa;
			$claveomesatasa=$claveomesatasa;
		}
		else{
			if ($seguro!="00"){
				$micodcur=$micodseguro;
				$cursotasa=$cursotasas;
				$claveomesatasa=$claveomesatasas;
			}
		}
		if ($bucle==0 || $seguro!="00"){

			$sql="SELECT * FROM CURSOS WHERE LTRIM(Cod)='".trim($micodcur)."'";
			
			
			$rec=bd::ejecuta($con,$sql,0,1);
			$cupo=bd::result($rec,"Cup")-bd::result($rec,"TopCal")-bd::result($rec,"ListaEspera");
			$emirec=adaptaCon('bit',bd::result($rec,"emirec"),3);
			$nummaxuso=bd::result($rec,"NumMaxUso");
			$dencurso=bd::result($rec,'Des');
			$tipcur=bd::result($rec,"TipCur");
			
			$maxdiasuso=bd::result($rec,'NumMaxDia');
			
			$tipreg=bd::result($rec,'TipReg');
			$pre=bd::result($rec,'Pre');
			$horaux=traduce2('ESCUELAS-CURSOS',trim($micodcur),'TEXHOR',bd::result($rec,'TexHor'));
			$diasem=bd::result($rec,'DiaSem');
			if ($_SESSION['tipc']!=="B"){
				$sql="SELECT CatUsu, ClaCajCur, ClaCajMat, ClaCajTar, Completo, ForPEsc, IniPago, FinPago, ";
				$sql.=" ImpCur,ImpCurNoE, ImpMat,ImpMatNoE,ImpTar, ImpTarNoE, ";
				$sql.=" AjusImp, ClaveOmesa,maxperalta,inipago,finpago FROM CURSOSTAS WHERE Cod = '".$cursotasa."'";
				$sql.=" AND ClaveOmesa = '".$claveomesatasa."'";

				
				$rec=bd::ejecuta($con,$sql,0,1);
				if ($empadronado==false || $empadronado=="NoE")
				$empadronado="NoE";
				else
				$empadronado="";
				$inip="";
				$finp="";
				if (bd::result($rec,"inipago")!=""){
					if (substr(bd::result($rec,"inipago"),0,1)=="*"){
						$tipo=substr(bd::result($rec,"inipago"),1,1);
						if ($tipo=="D")
						$inip=adddias(date('d/m/Y'),substr(bd::result($rec,"inipago"),2));
						if ($tipo=="M")
						$inip=addmes(date('d/m/Y'),substr(bd::result($rec,"inipago"),2));
						if ($tipo=="S")
						$inip=addano(date('d/m/Y'),substr(bd::result($rec,"inipago"),2));
						
					}
					else
					$inip=bd::result($rec,"inipago");
					
				}
				
				if (bd::result($rec,"finpago")!=""){
					if (substr(bd::result($rec,"finpago"),0,1)=="*"){
						$tipo=substr(bd::result($rec,"finpago"),1,1);
						if ($tipo=="D")
						$finp=adddias($inip,substr(bd::result($rec,"finpago"),2));
						if ($tipo=="M")
						$finp=addmes($inip,substr(bd::result($rec,"finpago"),2));
						if ($tipo=="S")
						$finp=addano($inip,substr(bd::result($rec,"finpago"),2));
						
					}
					else
					$finp=bd::result($rec,"finpago");
					
					
				}
				$maxperalta=bd::result($rec,"maxperalta");
				$tasausada=$clatasa;
				$clacajcur=bd::result($rec,'ClaCajCur');
				$clacajmat=bd::result($rec,'ClaCajMat');
				$clacajtar=bd::result($rec,'ClaCajTar');
				$cursocompleto=bd::result($rec,'Completo');
				$forpesc=bd::result($rec,'ForPEsc');
				$fecinipago=bd::result($rec,'IniPago');
				$fecfinpago=bd::result($rec,'FinPago');
				$ajusimp=bd::result($rec,'AjusImp');
				$importecurso=bd::result($rec,'ImpCur'.$empadronado);
				$importecursoinicial=$importecurso;
				$recibomat="";
				$matricula=1;
				if ($params['TipCli']=='VILCAN'){
					$sql="select usuarcur2.codcur from usuarcur2,cursos as cura,cursos as curb where  LTRIM(cura.Cod)='".trim($micodcur)."' and cura.temporada=curb.temporada and (usuarcur2.sitpag='AA' or usuarcur2.sitpag='PP' or usuarcur2.sitpag='FC') and curb.cod=usuarcur2.codcur and matsn='0' and usuarcur2.numabo='".$micodpers."'";
					$recaux=bd::ejecuta($con,$sql);
					if (bd::haydatos($recaux))
					$matricula=0;
					
				}
				if ($matricula==1){
					$importematricula=bd::result($rec,'ImpMat'.$empadronado);
					
					if ($tarjeta==0)
					$importecarnet=0;
					else
					$importecarnet=bd::result($rec,'ImpTar'.$empadronado);
				}
				else{
					$importematricula=0;
					$importecarnet=0;
				}
				if ($params['TipCli']=="BILBAO" && $tipc==1 && $tarjeta>0){
					
					$paradas=cargaParadas($complejo);
					if (is_array($paradas))
					foreach ($paradas as $pkey=>$pvalue){
						if ($pvalue['codigo']==$tarjeta){
							$obstarjeta=$pvalue['texto'];
						}
					}
					
				}
				if ($exmat==1)
					$importematricula=0;
				if ($extar==1)
					$importarcarnet=0;
				$idcaja='';
				$primerrecibogenerado='';
				$primeravuelta='';
				$lblfecdesper=array('','','','','','','','','','','','','');
				$lblfechasper=array('','','','','','','','','','','','','');
				$lbldiastopesr=array('','','','','','','','','','','','','');
				$lblporcenrecargo=array('','','','','','','','','','','','','');
				$lbldiaslimitepago=array('','','','','','','','','','','','','');
				$lblporcencobro=array('','','','','','','','','','','','','');
				$lblimpcobro=array('','','','','','','','','','','','','');
				$lbldialimitealta=array('','','','','','','','','','','','','');
				$lbldiaprincipioalta=array('','','','','','','','','','','','','');
				$lbldialimiterenov=array('','','','','','','','','','','','','');
				$lblcobrar=array('','','','','','','','','','','','','');
				$contaper=0;
				$numposforpesc=0;
				$heencontradouno=0;
				$pagounico=0;
				$i=0;
				$controlrecibo=0;
				$factual=date('d/m/Y');
				
				if ($fecalta!=$factual){
					$fauxalta=calculaFechaTmp($micodcur,$micodpers);
					if ($fauxalta=='')
						$fauxalta=$fecalta;
				}
				else
					$fauxalta=$fecalta;
				
			
				$factual2=date('d/m/Y');
			
				
				$partic[0]="";
				$periodoinicial="";
				$sql="SELECT Min(DesDia) as Desde, Max(HasDia) as Hasta FROM CURSOS2 WHERE LTRIM(Cod) = '".trim($micodcur)."'";
				
				$rec=bd::ejecuta($con,$sql,0,1);
				$fecini=adodb_date('d/m/Y',dttm2unixtime(bd::result($rec,"desde")));
				if (bd::result($rec,"hasta")!="")
				$fecfin=adodb_date('d/m/Y',dttm2unixtime(bd::result($rec,"hasta")));
				else
				$fecfin="";
			
				$sql="SELECT * FROM FORPESC_WEB WHERE Clave = '".$forpesc."' ";
				if ($params['canal']=='M')
					$sql="SELECT * FROM FORPESC_MAQ WHERE Clave = '".$forpesc."' ";
				if ($params['canal']=='P')
					$sql="SELECT * FROM FORPESC WHERE Clave = '".$forpesc."' ";
				
			
	
				$rec=bd::ejecuta($con,$sql,0,1);
				if (bd::haydatos($rec)) {
					for ($i=1;$i<=12;$i++) {
						
						$campo='IniPago'.str_pad($i,2,'0',STR_PAD_LEFT);
			
						if (''.bd::result($rec,$campo)!='') {
							if ($inip!="")
								$campoini=$inip;
							else
								$campoini=adodb_date('d/m/Y',dttm2unixtime(bd::result($rec,$campo)));
							$initmp=$campoini;
							$numposforpesc++;
							if ($finp!="")
								$campofin=$finp;
							else
								$campofin=adodb_date('d/m/Y',dttm2unixtime(bd::result($rec,'FinPago'.str_pad($i,2,'0',STR_PAD_LEFT))));
							if ($finp!=''){
								if (dias_entre_fechas(adodb_date('d/m/Y',dttm2unixtime(bd::result($rec,$campo))),$finp)<0){
									$campofin='01/01/1990';
								
								}
							}
							$campofin2=adodb_date('d/m/Y',dttm2unixtime(bd::result($rec,'FinPago'.str_pad($i,2,'0',STR_PAD_LEFT))));
							if ($params['TopeCursos2']==1  ||  $params['TipCli']=='BURGOS')
								if (dias_entre_fechas($campoini,$fecini)>0){
									$campoini=$fecini;
								}
							if ($params['TopeCursos2']==1 ||  $params['TipCli']=='BURGOS')
								if (dias_entre_fechas($campofin,$fecfin)<0){
									$campofin=$fecfin;
								}
							if (dias_entre_fechas($campoini,$fecalta2)>0){
									$campoini=$fecalta2;
							}
							if ($params['TopeCursos2']==1){
								if ((dias_entre_fechas($campoini,$fecfin)<=0 || dias_entre_fechas($fecini,$campofin)<=0) &&  $campoini!=$campofin){
									$campofin='01/01/1990';
								}
								
							}
							
			
							if (dias_entre_fechas($fecalta2,$campofin)>=0 && dias_entre_fechas($fecalta2,$campofin2)>=0 && $campofin!='01/01/1990'){
								
								$campo='DiaLimiteAlta'.str_pad($i,2,'0',STR_PAD_LEFT);
								$fechaaux=bd::result($rec,$campo);
								
								if ($fechaaux!='')
									$fechaaux=adodb_date('d/m/Y',dttm2unixtime($fechaaux));
								else
									$fechaaux='31/12/2015';
								$hfin=bd::result($rec,"HoraLimiteAlta".str_pad($i, 2, "0", STR_PAD_LEFT));
								if ($hfin=='')
									$hfin='24:00';
								else
									$hfin=adodb_date('H:i',dttm2unixtime($hfin));

	
								if (trim(''.bd::result($rec,$campo))=='' || (dias_entre_fechas($fecalta2, $fechaaux)>0 ||($fecalta2==$fechaaux && date('H:i')<=$hfin))) {
									$campo='DiaInicioAlta'.str_pad($i,2,'0',STR_PAD_LEFT);
									$fechaaux=bd::result($rec,$campo);
									
									if ($fechaaux!='')
									$fechaaux=adodb_date('d/m/Y',dttm2unixtime($fechaaux));
									if ($tipc=="C")
									$fechaaux=date('d/m/Y');
									if (dias_entre_fechas($campoini, date('d/m/Y'))>0)
									$fec=date('d/m/Y');
									else
									$fec=$campoini;
									
									if ($heencontradouno==0)
										$cup=$cupo-cupoFecha($micodcur,$campoini,$micodpers,0,$session_id);
									

									if ($cup>$usuadd || $controles==0){
										if ((trim(''.bd::result($rec,$campo))=='' || dias_entre_fechas($fechaaux,$fauxalta )>=0 || $_SESSION['tipc']=="B" ||  $heencontradouno==1)&& ($contaper<$maxperalta || $maxperalta==0)) {
											
											$heencontradouno=1;
											$lblfecdespertmp[$contaper]=$initmp;
											$lblfecdesper[$contaper]=$campoini;
											$lblfechasper[$contaper]=$campofin;
											if ($periodoinicial==""){
												$periodoinicial=$campoini.'-'.$campofin;
												$partic[0]=$campoini;
												$partic[1]=$campofin;
											}
											else{
												
												if ($contaper<$checkper){
													$periodoinicial.='<br>'.$campoini.'-'.$campofin;
													$partic[1]=$campofin;
												}
											}
											$campo='DiasTopeSR'.str_pad($i,2,'0',STR_PAD_LEFT);
											$lbldiastopesr[$contaper]=bd::result($rec,$campo);
											
											$campo='PorcenRecargo'.str_pad($i,2,'0',STR_PAD_LEFT);
											$lblporcenrecargo[$contaper]=bd::result($rec,$campo);
											
											$campo='DiasLimitePago'.str_pad($i,2,'0',STR_PAD_LEFT);
											$lbldiaslimitepago[$contaper]=bd::result($rec,$campo);
											
											$campo='PorcenCobro'.str_pad($i,2,'0',STR_PAD_LEFT);
											$lblporcencobro[$contaper]=bd::result($rec,$campo);
											
											$campo='ImpCobro'.str_pad($i,2,'0',STR_PAD_LEFT);
											$lblimpcobro[$contaper]=bd::result($rec,$campo);
											
											$campo='DiaLimiteAlta'.str_pad($i,2,'0',STR_PAD_LEFT);
											if (''.bd::result($rec,$campo)!='')
											$lbldialimitealta[$contaper]=adodb_date('d/m/Y',dttm2unixtime(bd::result($rec,$campo)));
											
											$campo='DiaPrincipioAlta'.str_pad($i,2,'0',STR_PAD_LEFT);
											if (''.bd::result($rec,$campo)!='')
											$lbldiaprincipioalta[$contaper]=adodb_date('d/m/Y',dttm2unixtime(bd::result($rec,$campo)));
											
											$campo='DiaLimiteRenov'.str_pad($i,2,'0',STR_PAD_LEFT);
											if (''.bd::result($rec,$campo)!='')
											$lbldialimiterenov[$contaper]=adodb_date('d/m/Y',dttm2unixtime(bd::result($rec,$campo)));

											$contaper++;
										}
									}
									
								}
							}
							if ($numposforpesc==1)
							$pagounico=1;
							else
							$pagounico=0;
						}
					}
				}
				
				$primerpago=-1;
				for ($i=0;$i<=11;$i++){
					if (trim($lblfecdesper[$i])!='') {
						$primerpago=$i;
						break;
					}
				}
				
				if ($primerpago==-1) {
					
					$respuesta['msg']=texto('Limite1');
					$respuesta['codigo']=0;
					limpiaBloqueoCurso($session_id,1);
					debug::saveError($args,'ALTACURSO',$respuesta['msg']);
					return $respuesta;
					
				}
				
				$contarperiodos=0;
				for ($i=0;$i<=11;$i++)
				if (trim($lblfecdesper[$i])!='')
				$contarperiodos++;
				

				if ($contarperiodos==0)
				$contarperiodos=1;
				$fecinipago='';
				$fecfinpago='';
				$importetotal=0;
				$importeparcial=0;
				$primero=1;
				
				// COGEMOS LOS IMPORTES DE LOS CURSOS
				$item=0;
				$predescuento=predescuento($checkper,$lblfecdesper,$lblfechasper,$descuento['cod']);
				$importecursoaux=$importecurso;
				$importecurso=0;
				$cursototal=0;
				for($i=0;$i<=11;$i++) {
					if ($lblfecdesper[$i]!='') {
						
						$importeparcial=number_format($importecursoaux*$lblporcencobro[$i]/100, 2, '.', '');
						$importeparcial=$importeparcial+$lblimpcobro[$i];
						if ($params['TipCli']=="MURCIA")
						$importeparcial=$importeparcial*$predescuento[$i];
						
						if ($primero==1) {
							
							$fecinipago=$lblfecdesper[$i];
							$fecfinpago=$lblfechasper[$i];
							$primero=0;
							
							
							$importeparcial=prorateo($importeparcial,$fecalta2,$lblfecdespertmp[$i],$fecfinpago,$ajusimp);	
							
						}
						if ($params['TipCli']!="MURCIA" || $checkper>$i){
							if ($descuento[0]!=0){
								$importeparcial=$importeparcial+($importeparcial*$descuento[0]/100);
							}
							if ($descuento[1]!=0){
								$importeparcial=$importeparcial+$descuento[1];
							}
							if ($importeparcial<0)
							$importeparcial=0;
							if ($checkper>$i)
							$importecurso=$importecurso+$importeparcial;
						}
						$cursototal=$cursototal+$importeparcial;
						
						$importeparcial=number_format($importeparcial, 2, '.', '');
						
						$lblcobrar[$i]=$importeparcial;
					}
				}
				$cursototal=number_format($cursototal, 2, '.', '');
				$impmat=$importematricula;
				if ($descuento[2]!=0)
				$impmat=$impmat+($impmat*$descuento[2])/100;
				if ($descuento[3]!=0)
				$impmat=$impmat+$descuento[3];
				if ($impmat<0)
				$impmat=0;
			
				if ($params['TipCli']=='FUENLA'){
			
					$auxf=explode('/',$fecinipago);
					$auxf=$auxf[1];
					if ($auxf>='09' && $auxf<='11'){
						$impmat=$impmat*3/3;
					}
					if ($auxf>='03' && $auxf<='05'){
						$impmat=$impmat/3;
					}
					if ($auxf=='12' || $auxf=='01' || $auxf=='02'){
						$impmat=$impmat*2/3;
					}
					$sql="select a.cod from usuarcur,cursos as a,cursos as b  where LTRIM(a.Cod)='".trim($micodcur)."' and a.act=b.act and a.pertie=b.pertie and usuarcur.numabo='".$micodpers."' and usuarcur.codcur=b.cod and usuarcur.estado='0'";
					$rec=bd::ejecuta($con,$sql);
					if (bd::haydatos($rec)){
						$impmat=0;
					}
				}		
				if ($params['TipCli']=="Etxebarri" && $descuento[0]=-10 && $impmat!=0)
				$impmat=$impmat+(($impmat*$descuento[0])/100);
				$imptar=$importecarnet;
				if ($descuento[4]!=0)
				$imptar=$imptar+($imptar*$descuento[4])/100;
				if ($descuento[5]!=0)
				$imptar=$imptar+$descuento[5];
				if ($imptar<0)
				$imptar=0;
				if ($params['TipCli']=="Etxebarri" && $descuento[0]=-10 && $imptar!=0)
				$imptar=$imptar+(($imptar*$descuento[0])/100);
				$importematricula=number_format($impmat, 2, '.', '');
				$importecarnet=number_format($imptar, 2, '.', '');
				if ($tiptas=='U'){
					$importecurso=$importecurso+$importecurso*$usuadd;
					$importematricula=$importematricula+$importematricula*$usuadd;
					$importecarnet=$importecarnet+$importecarnet*$usuadd;
				}
				if ($tipopago==10){
					$importecurso=0;
					$importematricula=0;
					$importecarnet=0;
				}
				$concimp1[1][1]=$clacajcur;
				$concimp1[1][2]=number_format($importecurso, 2, '.', '');
				$concimp1[1][3]=substr($micodcur.' '.$denabr,0,30);
				$concimp1[1][4]=1;
				
				$concimp1[2][1]=$clacajmat;
				$concimp1[2][2]=number_format($importematricula, 2, '.', '');
				$concimp1[2][3]='MATRICULA '.$micodcur;
				$concimp1[2][4]=1;
				
				
				$concimp1[3][1]=$clacajtar;
				$concimp1[3][2]=number_format($importecarnet, 2, '.', '');
				$concimp1[3][3]='TARJETA '.$micodcur;
				$concimp1[3][4]=1;
				
				if ($logincola!=""){
					if ($seguro==1){
						$concimp1[3][1]=$clacajtar;
						$concimp1[3][2]=$importecarnet;
						$concimp1[3][3]='SEGURO '.$micodcur;
						$concimp1[3][4]=1;
					}
					else{
						$importecarnet=0;
						$concimp1[3][1]=$clacajtar;
						$concimp1[3][2]=0;
						$concimp1[3][3]='SEGURO '.$micodcur;
						$concimp1[3][4]=1;
					}
					
				}
				$suplemento2=$suplemento;
				unset($suplemento2['cod']);
				foreach ($suplemento2 as $key=>$value){
					if (isset($value['imp']))
					if ($value['imp']!=0){
						$nuevo[1]=$value['cla'];
						$nuevo[2]=$value['imp'];
						$nuevo[3]='SUPLEMENTO '.$key;
						$concimp1=insertaImp($concimp1,$nuevo);
					}
				}
			}
			else{
				$importecurso=$importe;
				$importecursoinicial=$importe;
				$importeparcial=$importe;
				$importematricula=0;
				$importecarnet=0;
				$fecinipago=date('d/m/Y');
				$fecalta=date('d/m/Y');
				$fecfinpago=date('d/m/Y');
				$concimp1[1][1]=$clacaj;
				$concimp1[1][2]=$importe;
				$concimp1[1][3]=$destipent;
				$concimp1[1][4]=1;
				$concimp1[1][5]=$codtipent;
				$primerrecibogenerado="";
			}
			$conb=bd::globalCon(4);
			$conconta=bd::globalCon(5);
			if ($tipopago==1){
				if ($tpvvirtual!='')
					$medios[1]=$tpvvirtual;
				else
					$medios[1]=solPasarela();	
			
			}
			else
			$medios[1]="";
			if ($tipopago==4){
				$sql="select Cod from MEDCAJA where SolTarPre='1'";
				$recm=bd::ejecuta($conconta,$sql,0,1);
				$medios[1]=bd::result($recm,"Cod");
				$sql="select Cod,numtar from TARJETA where NumAbo = '".$micodpers."' and Est='0' and Importe > ImpGastado order by importe - impgastado";
				$recm=bd::ejecuta($con,$sql);
				$medios[2]=bd::result($recm,"numtar");
			}
			else
			$medios[2]="";
			if ($tipopago==8){
				$medios[1]=$params['MedCajaEfe'];
				
			}
			if ($tipopago==9){
				$medios[1]=$params['Datafono'];
				
			}
			if ($medcaja!='')
				$medios[1]=$medcaja;
			if ($_SESSION['tipc']!=="B")
			$impmedios[1]=$concimp1[1][2] + $concimp1[2][2] + $concimp1[3][2];
			else
			$impmedios[1]=$importe;
			$impmedios[1]=number_format($impmedios[1], 2, '.', '');
			if ($cursototal>0 && $tipopago==5){
					$respuesta['codigo']=0;
				$respuesta['msg']=texto('Aviso1');
				debug::saveError($args,'ALTACURSO',$respuesta['msg']);
				return $respuesta;
				exit;
			}
		
			if (dias_entre_fechas($fecinipago, $fecalta)>0)
			$uc2fecini=$fecalta;
			else
			$uc2fecini=$fecinipago;
			$uc2fecfin=$fecfinpago;
			
			//NumTarPre,FecIni, FecFin, NumOperacion, ReferPasarela,
			$datoscurso[1]=$medios[2];
			$datoscurso[2]=$uc2fecini;
			$datoscurso[3]=$uc2fecfin;
			$datoscurso[4]=$operacion;
			$datoscurso[5]=$referencia;
			
			
			$matdsn['reservas']['dsn']=$gdsn1;
			$matdsn['reservas']['user']=$guser1;
			$matdsn['reservas']['password']=$gpass1;
			$matdsn['reservas']['tipobd']=$gtipobd1;
			
			$matdsn['escuelas']['dsn']=$gdsn3;
			$matdsn['escuelas']['user']=$guser3;
			$matdsn['escuelas']['password']=$gpass3;
			$matdsn['escuelas']['tipobd']=$gtipobd3;
			
			$matdsn['conta']['dsn']=$gdsn5;
			$matdsn['conta']['user']=$guser5;
			$matdsn['conta']['password']=$gpass5;
			$matdsn['conta']['tipobd']=$gtipobd5;
			
			$matdsn['contadores']['dsn']=$gdsn4;
			$matdsn['contadores']['user']=$guser4;
			$matdsn['contadores']['password']=$gpass4;
			$matdsn['contadores']['tipobd']=$gtipobd4;
			
			
			if ($tipopago==4){
				$impaux=$importecurso+$importematricula+$importecarnet;
				if (!tarjetaimporte($con,$micodpers,$impaux)){
					$respuesta['msg']=texto('Limite2');
					$respuesta['codigo']=0;
					limpiaBloqueoCurso($session_id,1);
					debug::saveError($args,'ALTACURSO',$respuesta['msg']);
					return $respuesta;
					
				}
			}
			//Si es anónimo, creamos ficha
			if ($logincola!="" && $micodpers!="ZZZZZZ" && $micodpers!=""){
				$sql="update usuar set codcol='".$logincola."' where NumAbo='".$micodpers."'";
				$rec=bd::ejecuta($con,$sql);
			}
			
			
			$numtic="";
			
			$numtic=contador("ESCUELAS",9,9,1);
			if ($bucle==0){
				$numticaux=$numtic;
			}	
			$idcaja='';
			if ($_SESSION['tipc']==="B")
			$origen=1;
			else
			$origen=0;
			if ($_SESSION['tipc']==="B")
				$valor=apuntescaja($origen,$concimp1, $micodpers,$numtic,trim($codtipent), $idcaja, date('d/m/Y'), $matdsn, $medios, $impmedios,$datoscurso,"",0,"",$entradas2);
			else{
				$valor=apuntescaja($origen,$concimp1, $micodpers,$numtic,trim($micodcur), $idcaja, date('d/m/Y'), $matdsn, $medios, $impmedios,$datoscurso);
				
			}
			
			if ($medios[1]=="")
			$idcaja="";
			$fechahoy=date('d/m/Y');
			$sql="SELECT Min(DesDia) as Desde, Max(HasDia) as Hasta FROM CURSOS2 WHERE LTRIM(Cod) = '".trim($micodcur)."'";
			
			$rec=bd::ejecuta($con,$sql,0,1);
			$fecini=$fecalta;
			$fecfin=$fecalta;
			if (bd::haydatos($rec)) {
				
				if (bd::result($rec,"Desde")!='') {
					$fechaaux=bd::result($rec,"Desde");
					$fechaaux=adodb_date('d/m/Y',dttm2unixtime($fechaaux));
					
					if (dias_entre_fechas($fechahoy, $fechaaux)>=0)
						$fecini=$fechaaux;
					else
						$fecini=$fechahoy;
				}
				else
					$fecini=$fechahoy;
				
				if (bd::result($rec,"Hasta")!='') {
					
					$fechaaux=bd::result($rec,"Hasta");
					$fechaaux=adodb_date('d/m/Y',dttm2unixtime($fechaaux));
					
					$tmp = explode('/',$fechahoy);
					$dia1=$tmp[0];
					$mes1 = $tmp[1];
					$anyo1 = $tmp[2];
					if ($maxdiasuso=='')
						$maxdiasuso=0;
				
					if (substr($maxdiasuso,0,1)=='M')
						$fechaauxbis=adodb_mktime(0,0,0,$mes1+(int)substr($maxdiasuso,1),0,$anyo1);
					else if (substr($maxdiasuso,0,1)=='D')
						$fechaauxbis=adodb_mktime(0,0,0,$mes1,$dia1+(int)substr($maxdiasuso,1),$anyo1);
					else if (substr($maxdiasuso,0,1)=='A')
						$fechaauxbis=adodb_mktime(0,0,0,1,0,$anyo1+(int)substr($maxdiasuso,1));
					else
						$fechaauxbis=adodb_mktime(0,0,0,$mes1,$dia1+(int)$maxdiasuso,$anyo1);
					$fechaauxbis=adodb_date('d/m/Y',$fechaauxbis);
					if (dias_entre_fechas(date('d/m/Y'), $fechaauxbis)<=0){
						$fechaauxbis='01/01/2036';
					}
					
					if (dias_entre_fechas($fechaaux, $fechaauxbis)>0)
					$fecfin=$fechaaux;
					else
					$fecfin=$fechaauxbis;
					
					if ($params['TipCli']=='PMD')
					$fecfin=$fechaaux;
				}
				else
				$fecfin=$fecalta;
				
			}
			else {
				$fecini=$fecalta;
				$fecfin=$fecalta;
			}
			
			$numbon='';
			$numuso=0;
		
			if ($tipreg=='B' ||$tipreg=='P' || $tipreg=='A'  || $tipreg=='S' || $tipreg=='H') {
				
				$numbon=contador("ESCUELAS",6,10);
		
				//calculamos el fecfin;
				if (substr($maxdiasuso,0,1)=='M')
						$fmax=adodb_mktime(0,0,0,date('m')+(int)substr($maxdiasuso,1),date('d'),date('Y'));
					else if (substr($maxdiasuso,0,1)=='D')
						$fmax=adodb_mktime(0,0,0,date('m'),date('d')+(int)substr($maxdiasuso,1),date('Y'));
					else
						$fmax=adodb_mktime(0,0,0,date('m'),date('d')+(int)$maxdiasuso,date('Y'));
			
				
				
				if (dias_entre_fechas($fmax,$fecfin)>0)
				$fmax=$fecfin;
				
				if ($params['TipCli']!='PMD')
				$fecfin=$fmax;
				$sql="INSERT INTO BONOS (Cod, Abonado, Apel, Nom, FEmi, FVto, Estado, NumTar";
				
				$sql.=",Descripcion,TipReg,codent,nummaxuso,bononominal)";
				
				$sql.=" VALUES (";
				$sql.="'".$numbon."', ";
				$sql.="'".$micodpers."', ";
				$sql.="'".$apellidos."', ";
				$sql.="'".$nomabo."', ";
				$sql.="'".adaptaCon('Fec',$fechahoy,3)."', ";
				$sql.="'".adaptaCon('Fec',$fmax,3)."', ";
				$sql.="'0', '".$numtar."'";
				
				$sql.=",'".$destipent."','".$tipreg."','".$codtipent."','".$nummaxuso."','".$bononominal."')";
				
				$rec=bd::ejecuta($con,$sql);
			}
			if ($tipreg=='P' || $tipreg=='H'){
				$numuso=$nummaxuso;
				
				$sql="select * from TIPENTRAINS where Cod='".$codtipent."'";
				$recbono=bd::ejecuta($con,$sql);
				for ($caux=1;$caux<=10;$caux++){
					if ($caux!=10)
					$codinst[$caux]=bd::result($recbono,"CodIns0".$caux);
					else
					$codinst[$caux]=bd::result($recbono,"CodIns".$caux);
				}
				for ($caux=1;$caux<=5;$caux++){
					for ($caux2=1;$caux2<=10;$caux2++){
						if ($caux2!=10)
						$supins[$caux][$caux2]=bd::result($recbono,"Sup".$caux."Ins0".$caux2);
						else{
							
							$supins[$caux][$caux2]=bd::result($recbono,"Sup".$caux."Ins".$caux2);
							
						}
					}
				}
			}
			if ($tipreg=='S'){
				$numuso=$nummaxuso;
				
				$sql="select * from TIPENTRASER where Cod='".$codtipent."'";
				$recbono=bd::ejecuta($con,$sql);
				for ($caux=1;$caux<=10;$caux++){
					if ($caux!=10)
					$codinst[$caux]=bd::result($recbono,"CodSer0".$caux);
					else
					$codinst[$caux]=bd::result($recbono,"CodSer".$caux);
				}
				for ($caux=1;$caux<=5;$caux++){
					for ($caux2=1;$caux2<=10;$caux2++){
						if ($caux2!=10)
						$supins[$caux][$caux2]=0;
						else{
							
							$supins[$caux][$caux2]=0;
							
						}
					}
				}
			}
			if ($tipreg=='S' || $tipreg=='P'){
				$hora=date('H:i:s');
				for ($nlim=1;$nlim<=$numuso;$nlim++){
					
					$numerolinea=str_pad($nlim,2,'0',STR_PAD_LEFT);
					$sql="INSERT INTO BONOSLIN (Cod,FecE,HorE,NumUso,CodIns01,CodIns02,CodIns03,CodIns04,CodIns05,CodIns06,CodIns07,CodIns08,CodIns09,CodIns10,Sup1Ins01,Sup1Ins02,Sup1Ins03,Sup1Ins04,Sup1Ins05,Sup1Ins06,Sup1Ins07,Sup1Ins08,Sup1Ins09,Sup1Ins10,Sup2Ins01,Sup2Ins02,Sup2Ins03,Sup2Ins04,Sup2Ins05,Sup2Ins06,Sup2Ins07,Sup2Ins08,Sup2Ins09,Sup2Ins10,Sup3Ins01,Sup3Ins02,Sup3Ins03,Sup3Ins04,Sup3Ins05,Sup3Ins06,Sup3Ins07,Sup3Ins08,Sup3Ins09,Sup3Ins10,Sup4Ins01,Sup4Ins02,Sup4Ins03,Sup4Ins04,Sup4Ins05,Sup4Ins06,Sup4Ins07,Sup4Ins08,Sup4Ins09,Sup4Ins10,Sup5Ins01,Sup5Ins02,Sup5Ins03,Sup5Ins04,Sup5Ins05,Sup5Ins06,Sup5Ins07,Sup5Ins08,Sup5Ins09,Sup5Ins10) Values (";
					$sql.="'".$numbon."', ";
					$sql.="'".adaptaCon('Fec',date('d/m/Y'),3)."', ";
					
					
					$sql.="'".adaptaCon('Hor',$hora,3)."', ";
					$hora=sumaSegundos($hora,1);
					$sql.="'".$numbon.$numerolinea."', ";
					for ($caux=1;$caux<=10;$caux++)
					$sql.="'".$codinst[$caux]."', ";
					for ($caux=1;$caux<=5;$caux++){
						for ($caux2=1;$caux2<=10;$caux2++){
							if ($caux==5 && $caux2==10)
							$sql.="'".$supins[$caux][$caux2]."') ";
							else
							$sql.="'".$supins[$caux][$caux2]."', ";
							
						}
					}
					
					$recbono=bd::ejecuta($con,$sql);
					
				}
			}
			if ($tipreg=='H'){
				$hora=date('H:i:s');
				
				
				
				$sql="INSERT INTO BONOSLIN (Cod,FecE,HorE,NumUso,CodIns01,CodIns02,CodIns03,CodIns04,CodIns05,CodIns06,CodIns07,CodIns08,CodIns09,CodIns10,Sup1Ins01,Sup1Ins02,Sup1Ins03,Sup1Ins04,Sup1Ins05,Sup1Ins06,Sup1Ins07,Sup1Ins08,Sup1Ins09,Sup1Ins10,Sup2Ins01,Sup2Ins02,Sup2Ins03,Sup2Ins04,Sup2Ins05,Sup2Ins06,Sup2Ins07,Sup2Ins08,Sup2Ins09,Sup2Ins10,Sup3Ins01,Sup3Ins02,Sup3Ins03,Sup3Ins04,Sup3Ins05,Sup3Ins06,Sup3Ins07,Sup3Ins08,Sup3Ins09,Sup3Ins10,Sup4Ins01,Sup4Ins02,Sup4Ins03,Sup4Ins04,Sup4Ins05,Sup4Ins06,Sup4Ins07,Sup4Ins08,Sup4Ins09,Sup4Ins10,Sup5Ins01,Sup5Ins02,Sup5Ins03,Sup5Ins04,Sup5Ins05,Sup5Ins06,Sup5Ins07,Sup5Ins08,Sup5Ins09,Sup5Ins10) Values (";
				$sql.="'".$numbon."', ";
				$sql.="'".adaptaCon('Fec',date('d/m/Y'),3)."', ";	
				$sql.="'".adaptaCon('Hor',$hora,3)."', ";
				$hora=sumaSegundos($hora,1);
				$sql.="'".$numuso."', ";
				for ($caux=1;$caux<=10;$caux++)
				$sql.="'".$codinst[$caux]."', ";
				for ($caux=1;$caux<=5;$caux++){
					for ($caux2=1;$caux2<=10;$caux2++){
						if ($caux==5 && $caux2==10)
						$sql.="'".$supins[$caux][$caux2]."') ";
						else
						$sql.="'".$supins[$caux][$caux2]."', ";
						
					}
				}
				
				$recbono=bd::ejecuta($con,$sql);
				
			}
			
			
			if ($tipreg=='A'){
				$numuso=$nummaxuso;
				
				$sql="select * from TIPENTRAACT where Cod='".$codtipent."'";
				
				$recbono=bd::ejecuta($con,$sql);
				for ($caux=1;$caux<=20;$caux++){
					if ($caux<10)
					$codinst[$caux]=bd::result($recbono,"CodAct0".$caux);
					else
					$codinst[$caux]=bd::result($recbono,"CodAct".$caux);
				}
				$hora=date('H:i:s');
				for ($nlim=1;$nlim<=$numuso;$nlim++){
					$numerolinea=str_pad($nlim,2,'0',STR_PAD_LEFT);
					$sql="INSERT INTO BONOSLIN (Cod,Ope,FecE,HorE,NumUso,CodAct01,CodAct02,CodAct03,CodAct04,CodAct05,CodAct06,CodAct07,CodAct08,CodAct09,CodAct10,CodAct11,CodAct12,CodAct13,CodAct14,CodAct15,CodAct16,CodAct17,CodAct18,CodAct19,CodAct20) Values (";
					$sql.="'".$numbon."', ";
					$sql.="'".$params['IdenPuesto']."', ";
					$sql.="'".adaptaCon('Fec',date('d/m/Y'),3)."', ";
					
					$sql.="'".adaptaCon('Hor',$hora,3)."', ";
					$hora=sumaSegundos($hora,1);
					$sql.="'".$numbon.$numerolinea."' ";
					for ($caux=1;$caux<=20;$caux++)
					$sql.=",'".$codinst[$caux]."' ";
					
					
					$sql.=")";
					$recbono=bd::ejecuta($con,$sql);
				}
			}
			
			
			$codseguro='00'; $descseguro='';
			$sql="SELECT Cod, Descrip FROM SEGUROS WHERE Cod = '".$seguro."'";
			$rec=bd::ejecuta($con,$sql,0,1);//($rec, $sql)
			$codseguro=bd::result($rec,'Cod');
			$descseguro=bd::result($rec,'Descrip');
			
			$importeparcial=number_format($importeparcial, 2, '.', '');
			$importetotal=$importecurso+$importematricula+$importecarnet;
			$importetotal=number_format($importetotal, 2, '.', '');
			$importecurso=number_format($importecurso,2,'.','');
			if ($tipreg=='B' ||$tipreg=='P' || $tipreg=='A' || $tipreg=='H') {
				$importeparcial=$importetotal;
			}
			
			
			
			$cadenasuplemento="";
			$cadenasuplemento2="";
			
			
			for ($x=0;$x<10;$x++){
				if ($suplemento[$x]['des']!=""){
					$cadenasuplemento.=",opccur".$x.",impopc".$x.",clacajopc".$x.",soloalta".$x;
					$cadenasuplemento2.=",'".$suplemento[$x]['des']."',".$suplemento[$x]['imp'].",'".$suplemento[$x]['cla']."','".$suplemento[$x]['alta']."'";
					if ($especial['suple']!=''){
						$especial['suple'].='<br>';
					}
					$importetotal=$importetotal+$suplemento[$x]['imp'];
					$especial['suple'].=$suplemento[$x]['des'].' - '.$suplemento[$x]['imp'].' EUROS';
				}
			}
			if ($tipopago==10){
				$importetotal=0;
				$importecursoinicial=0;
				$importematricula=0;
				$importecarnet=0;
			}
			$importetotal=number_format($importetotal, 2, '.', '');
			
			$sql="INSERT INTO USUARCUR (esfechalibre,TrabCredito,CatUsu, CodRed, NumAbo, CodCur, Den, ImpPag, ";
			$sql.=" CurCom, ImpCur, ClaCajCur, ImpCurDev, ImpMat, ClaCajMat, ImpMatDev, ";
			$sql.=" ImpTar, ClaCajTar, ImpTarDev, FechaAlta, IdCaja, UltImpCobrado, ";
			$sql.=" FechaCobrada, FechaUltCobro, ForPEsc, FecIni, FecFin, Estado, NumBon, CodSeguro, DescSeguro,AltaCanal,NumUso,AltaColabor,Observaciones".$cadenasuplemento.",actcredito,solcredlru,codnivel,credconcedido,solcredects,numabo0,numabo1,numabo2,numabo3,numabo4,numabo5,numabo6,numabo7,numabo8,numabo9,justificar,RefJustificacion,idiomasolicitado,ClaveOmesaCursoAdm,codcamp,descamp";
			if ($_SESSION['tipc']!=="B")
			$sql.=",ClaveOmesaCursosTas)";
			else
			$sql.=")";
			
			$sql.=" VALUES ('".$curso1dia."','N',";
			$sql.="'".$tasausada."', ";
			$sql.="'".$reducusada."', ";
			$sql.="'".$micodpers."', ";
			$sql.="'".$micodcur."', ";
			$sql.="'".$dencurso."', ";
			if ($tipopago==1 ||$tipopago==4 ||$tipopago==5 ||$tipopago==8 || $tipopago==10 || $tipopago==9)
			$sql.="'".$importetotal."', ";
			else
			$sql.="'0', ";
			$sql.="'".$cursocompleto."', ";
			$sql.=$importecursoinicial.", ";
			$sql.="'".$clacajcur."', ";
			$sql.="'0', ";
			$sql.=$importematricula.", ";
			$sql.="'".$clacajmat."', ";
			$sql.="'0', ";
			$sql.="'".$importecarnet."', ";
			$sql.="'".$clacajtar."', ";
			$sql.="'0', ";
			$sql.="'".adaptaCon('Fec',$fecalta,3)."', ";
			$sql.="'', ";
			if ($tipopago==1 ||$tipopago==4 ||$tipopago==5||$tipopago==8 || $tipopago==10 || $tipopago==9)
			$sql.="'".$importetotal."', ";
			else
			$sql.="'0', ";
			$sql.="'".adaptaCon('Fec',$fecalta,3)."', ";
			$sql.="'".adaptaCon('Fec',$fecalta,3)."', ";
			$sql.="'".$forpesc."', ";
			$sql.="'".adaptaCon('Fec',$fecini,3)."', ";
			$sql.="'".adaptaCon('Fec',$fecfin,3)."', ";
			if ($curso1dia==1 && ($tipopago==1 ||$tipopago==4 ||$tipopago==5||$tipopago==8||$tipopago==10 || $tipopago==9))
			$sql.="'1', ";	
			else
			$sql.="'0', ";
			$sql.="'".$numbon."', ";
			$sql.="'".$codseguro."', ";
			$sql.="'".$descseguro."', '".$params['canal']."', '".$numuso."','".$logincola."'";
			$sql.=",'".$observaciones."'";
			$sql.=$cadenasuplemento2;
			
			if ($numcred<>0)
			$sql.=",'1'";
			else
			$sql.=",'0'";
			$sql.=",'".$credi1."','".$codnivel."','0','".$credi2;
			$sql.="','".$jugadores[0]."','".$jugadores[1]."','".$jugadores[2]."','".$jugadores[3]."','".$jugadores[4]."','".$jugadores[5]."','".$jugadores[6]."','".$jugadores[7]."','".$jugadores[8]."','".$jugadores[9]."','".$justificar."','".$texjusti."','".$idiomacurso."','".$clave."','".$camp."','".$descamp."'";
			
			if (($params['Version']=="2010" || $params['Version']=='BOOTSTRAP') && $_SESSION['tipc']!=="B")
			$sql.=",'".$claveomesatasa."')";
			else
			$sql.=")";
			
			
			$rec=bd::ejecuta($con,$sql);
			
			$dia1 = strtok($fecini, "/");
			$mes1 = strtok("/");
			$anyo1 = strtok("/");
			$fecinibuscar=convertirfecha($dia1,$mes1,$anyo1,$gtipobd3);
			
			$dia1 = strtok($fecfin, "/");
			$mes1 = strtok("/");
			$anyo1 = strtok("/");
			$fecfinbuscar=convertirfecha($dia1,$mes1,$anyo1,$gtipobd3);
			
			$sql="SELECT ".adaptaCon('top',1,7)." ClaveOmesa FROM USUARCUR WHERE ";
			$sql.=" NumAbo = '".$micodpers."'";
			$sql.=" AND CodCur = '".$micodcur."'";
			$sql.=" AND (Estado = '0' or estado='1')";
			$sql.=" AND FecIni = '".adaptaCon('fec',$fecini,3)."' ";
			$sql.=" AND FecFin = '".adaptaCon('fec',$fecfin,3)."' ";
			$sql.=" ORDER BY ClaveOmesa DESC ".adaptaCon('lim',1,7);
			
			$rec=bd::ejecuta($con,$sql);
			if (bd::haydatos($rec))
			$claveomesausuarcur = bd::result($rec,'ClaveOmesa');
			else {
				
		        echo texto('Limite3');
				echo '<script>window.location="javascript:history.go(-3)";</script>';
				
				exit;
			}
			$respuesta['usuarcur'] = $claveomesausuarcur;
			foreach ($suplemento as $key=>$value){
				if (isset($value['cla']))
				if ($value['cla']!=""){
					$contarperiodos++;
				}
			}
			
			
			
			
			
			$importeparcial=$importematricula+$importecarnet;
			
			if ($importeparcial!=0 && $juntarecibos==0){
				$contarperiodos++;
			}
			$contaux=contador("ESCUELAS",4,-2,0,8,$contarperiodos);
			$numcont=$contaux['cont'];
			$sera=$contaux['pref'];
			$serb=$contaux['suf'];
			
			
			
			
			// ANOTAR IMPORTE DE MATRÍCULA MÁS CARNET
			
			$fecinipago=$fecalta;
			$fecfinpago=$fecalta;
			
			$uc2claveusuarcur=$claveomesausuarcur;
			$uc2numabo=$micodpers;
			$uc2codcur=$micodcur;
			$uc2fecgen=$fechahoy;
			$uc2sitpag='FC';
			$uc2feccobro='0:00:00';
			$uc2imppend=$importeparcial;
			if ($params['TipCli']!='PMD') {
				if (dias_entre_fechas($fecinipago, $fecalta)>0)
				$uc2fecini=$fecalta;
				else
				$uc2fecini=$fecinipago;
				$uc2fecfin=$fecfinpago;
			}
			$uc2diastopesr=0;
			$uc2porcenrecargo=0;
			$uc2diaslimitepago=0;
			$uc2imppago=number_format($importeparcial-$uc2imppend,2,'.','');
			$uc2diasprincipioalta=NULL;
			$uc2diaslimiterenov=NULL;
			$uc2diaslimitealta=NULL;
			$uc2idcaja=$idcaja;
			
			
			
			$cagru=contador("ESCUELAS",12,0);
			if ($importeparcial!=0 && $juntarecibos==0){
				$importeparcial=number_format($importeparcial, 2, '.', '');
				$numcont=$numcont+1;
				if ($sera=='')
					$lenpref=0;
				else
					$lenpref=strlen($sera);
				if ($serb==0)
					$lensuf=0;
				else
					$lensuf=strlen($serb);
				$numrec=$sera.transformacontador($numcont,6-$lenpref-$lensuf).$serb;
				
				$numrec=str_pad($numrec,6,' ',STR_PAD_LEFT);
				$recibos[$contarecibos]=$numrec;
				$contarecibos++;
				$fecinipago=date('d/m/Y');
				$fecfinpago=date('d/m/Y');
				
				$uc2claveusuarcur=$claveomesausuarcur;
				$uc2numabo=$micodpers;
				$uc2codcur=$micodcur;
				$uc2fecgen=$fechahoy;
				$uc2sitpag='FC';
				$uc2feccobro='0:00:00';
				$uc2imppend=$importeparcial;
				
				if (dias_entre_fechas($fecinipago, $fecalta)>0)
				$uc2fecini=$fecalta;
				else
				$uc2fecini=$fecinipago;
				$uc2fecfin=$uc2fecini;
				
				$uc2diastopesr=0;
				$uc2porcenrecargo=0;
				$uc2diaslimitepago=0;
				$uc2imppago=number_format($importeparcial-$uc2imppend,2,'.','');
				$uc2diasprincipioalta=NULL;
				$uc2diaslimiterenov=NULL;
				$uc2diaslimitealta=NULL;
				
				$uc2idcaja=$idcaja;
				$uc2numrec=$numrec;
				if ($tipopago==10)
					$uc2imppend=0;
				$sql="INSERT INTO USUARCUR2 (esfechalibre,ClaveUsuarCur, NumAbo, CodCur, FecGen, SitPag, ";
				$sql.=" ImpPend, FecCobro, FecIni, FecFin, DiasTopeSR, PorcenRecargo, ";
				$sql.=" DiasLimitePago, ImpPag, IDCaja, NumRec, CodRed, DiaPrincipioAlta, ";
				$sql.=" DiaLimiteAlta, DiaLimiteRenov,MatSN";
				if ($_SESSION['tipc']!=="B")
				$sql.=",ClaveOmesaCursosTas";
				
				$sql.=") VALUES (".$curso1dia.", ";
				$sql.="'".$uc2claveusuarcur."', ";
				$sql.="'".$uc2numabo."', ";
				$sql.="'".$uc2codcur."', ";
				$sql.="'".adaptaCon('Fec',$uc2fecgen,3)."', ";
				if ($tipopago==1 || $tipopago==4 ||$tipopago==5||$tipopago==8||$tipopago==10 || $tipopago==9)
				$sql.="'AA', ";
				else{
					if ($tipopago=='C')
						$sql.="' C',";
					else
						$sql.="'".$uc2sitpag."', ";
				}
				if ($tipopago==1 || $tipopago==4 ||$tipopago==5||$tipopago==8||$tipopago==10 || $tipopago==9)
				$sql.="'0', ";
				else
				$sql.=$uc2imppend.", ";
				if ($tipopago==1 || $tipopago==4 ||$tipopago==5||$tipopago==8||$tipopago==10 || $tipopago==9)
				$sql.="'".adaptaCon('Fec',$uc2fecgen,3)."', ";
				else
				$sql.="NULL, ";
				
				$sql.="'".adaptaCon('Fec',$uc2fecini,3)."', ";
				$sql.="'".adaptaCon('Fec',$uc2fecfin,3)."', ";
				$sql.=$uc2diastopesr.", '";
				$sql.=$uc2porcenrecargo."', ";
				$sql.="'".$uc2diaslimitepago."', '";
				if ($pago==1)
				$sql.=$importeparcial."', ";
				else
				$sql.=$uc2imppago."', ";
				$sql.="'".$uc2idcaja."', ";
				$sql.="'".$uc2numrec."', ";
				$sql.="'".$reducusada."', ";
				$sql.="NULL, ";
				$sql.="NULL, ";
				$sql.="NULL,'1'";
				if ($_SESSION['tipc']!=="B")
				$sql.=",'".$claveomesatasa."'";
				$sql.=")";
				$rec=bd::ejecuta($con,$sql);
				
				//obtenemos valores para codrefusu 1 y 2
				
				
				$ref2="";
				
				$dia1 = strtok($fecinipago, "/");
				$mes1 = strtok("/");
				$anyo1 = strtok("/");
				$fechaaux=mktime(0,0,0,$mes1,$dia1+$lbldiaslimitepago[0],$anyo1);
				$rmodpago=$pago;
				if ($pagdom=='S')
				$rmodpago='2';
				else
				$rmodpago='1';
				if ($params['TipCli']=='BILBAO' || $params['TipCli']=='BASAURI' ||$params['TipCli']=="DURANGO") {
					if ($tipopago!=2 && $tipopago!=3)
					$rmodpago='1';
				}
				if ($params['TipCli']=='JOLASETA'){
					if ($tipopago!=2){
						$rmodpago='1';
					}
				}
				if ($emirec==true)
				$rmodpago=1;
				
				if ($tipopago==1 || $tipopago==4 ||$tipopago==5||$tipopago==8||$tipopago==10 || $tipopago==9){
					$rest='1';
					$tcadrec=2;
				}
				else{
					$rest='0';
					$tcadrec=0;
				}
				if ($totaljugadores>1 && $params['TipCli']=="JOLASETA_FUTURAMODIFICACION")
				$rpendiente=number_format($importeparcial/$totaljugadores,2,'.','');
				else
				$rpendiente=$importeparcial;
				$rcobrado=0;
				if ($tipopago=='C'){
					$rest='C';
				}
				$cadenarecibo=obsticket(1,$tipopago);
				
				$sql="INSERT INTO RECIBAN (NumRec, CodRef2Usu, FecRec, FechLimPago, Est, CodUsu, ApeNom,Cif, Tit, Ent, Ofi,DigCon, Cue,paisIBAN,digconIBAN, ImpRec, Con, Cla , Ordenante, Origen, ModPago, Pendiente, Cobrado,Observaciones,tipousu,facturado,numabofac,imprecori,serfac,eslineafac,claveagru)";
				$sql.="VALUES (";
				$sql2="'".$numrec."', ";
				$sql3="'".$ref2."', '".adaptaCon('Fec',$fecinipago,3)."', '".adaptaCon('Fec',date("d/m/Y",$fechaaux),3)."','".$rest."',";
				$cadenausu="'".$micodpers."', '".substr($apellidos.", ".$nomabo, 0, 30)."', '".$cifabo."', '".$titabo."', '".$datosusu['ent']."', '".$datosusu['suc']."', '".$datosusu['digcon']."', '".$datosusu['cue']."', '".$datosusu['paisIBAN']."','".$datosusu['digconIBAN']."',";
				
				$sql4=$importeparcial.", 'MATRICULA CURSO ".trim($micodcur)."', '".$micodcur."', '".$ordenante."', '0', '".$rmodpago."', ";
				if ($tipopago==1 || $tipopago==4 ||$tipopago==5||$tipopago==8 || $tipopago==9){
					$sql4.="'0', '";
					$sql4.=$rpendiente."'";
					if ($params['Contabilidad']==1 || ($params['Contabilidad']==2 && ($tipopago==1 || $tipopago==8 || $tipopago==9)))
					$recibosconta[$numrec]=$rpendiente;
				}
				else{
					$sql4.="'".$rpendiente."','";
					$sql4.=$rcobrado."'";
					
				}
				if ($rest=='0'){
					$recibosvb[]=$numrec.'-'.$fechaaux.'-'.$rpendiente;
				}
				$sql4.=",'".$cadenarecibo."','0','0','".$numabofac."','".$importeparcial."','".$confac."','1','".$cagru."')";
				
				$rec=bd::ejecuta($con,$sql.$sql2.$sql3.$cadenausu.$sql4);
			
				BuscaConcepto($numrec,$con,$con2);
				if ($totaljugadores>1 && $params['TipCli']=="JOLASETA_FUTURAMODIFICACION"){
					foreach ($cadenajug as $key=>$value){
						$sqlconta="SELECT Prefijo, Contador, Sufijo FROM ESCUELASCONTADO2 WHERE Clave = '4'";
						$recb=bd::ejecuta($conb,$sqlconta);
						$sera=bd::result($recb,'Prefijo');
						$serb=bd::result($recb,'Sufijo');
						$numconta=bd::result($recb,'Contador')+1;
						$numreca=$sera.transformacontador($numconta,6-strlen($sera)-strlen($serb)).$serb;
						$numreca=str_pad($numreca,6,' ',STR_PAD_LEFT);
						$sql2="'".$numreca."', ";
						$contarperiodos++;
						$sqlconta="UPDATE ESCUELASCONTADO2 SET Contador = Contador + 1 WHERE Clave = '4'";
						$recb=bd::ejecuta($conb,$sqlconta);
						$rec=bd::ejecuta($con,$sql.$sql2.$sql3.$cadenajug[$key].$sql4);
					}
				}
				
				$recibomat=$numrec;
				
				$ref2aux=$ref2;
				// FIN DE ANOTAR IMPORTE DE MATRÍCULA MÁS CARNET
				
				
				
			}
			
				
			$raux=generaRecibosSuplementos($suplemento,$micodcur,$micodpers,$claveomesausuarcur,$tipopago,$idcaja,$datosusu,$reducusada,$numcont,$sera,$serb,$ordenante,$cadenarecibo,$confac,$cagru);


			if (is_array($raux)){
				foreach ($raux as $key=>$value){
					$recibosconta[$key]=$value;
				}
			}
			
			if ($importeparcial!=0 && $juntarecibos==1)
			$lblcobrar[0]=$lblcobrar[0]+$importeparcial;
			if ($_SESSION['tipc']!=="B"){
				
				
				$fecfinalta=date('d/m/Y');
				
				for ($i=0;$i<=11;$i++) { 
					
					if ($lblfecdesper[$i]!='') {
						
						$numcont=$numcont+1;
						$numrec=$sera.transformacontador($numcont,6-strlen((string)$sera)-strlen((string)$serb)).$serb;
						$numrec=str_pad($numrec,6,' ',STR_PAD_LEFT);
						if ($checkper>$i || $tipopago==10){
							$recibos[$contarecibos]=$numrec;
							$contarecibos++;
						}
						$fecinipago=$lblfecdesper[$i];
						$fecfinpago=$lblfechasper[$i];
						if ($lblcobrar[$i]=='')
							$importeparcial=0;
						else{
							if ($tiptas=='G')
								$importeparcial=$lblcobrar[$i];
							else
								$importeparcial=$lblcobrar[$i]+$lblcobrar[$i]*$usuadd;
						}
						
						$uc2claveusuarcur=$claveomesausuarcur;
						$uc2numabo=$micodpers;
						$uc2codcur=$micodcur;
						$uc2fecgen=$fechahoy;
						$uc2sitpag='FC';
						$uc2feccobro='0:00:00';
						$uc2imppend=$importeparcial;
						if ($params['TipCli']!='PMD') {
							if (dias_entre_fechas($fecinipago, $fecalta)>0)
								$uc2fecini=$fecalta;
							else
								$uc2fecini=$fecinipago;
							$uc2fecfin=$fecfinpago;
						}
						else{
							$uc2fecini=$fecinipago;
							$uc2fecfin=$fecfinpago;
						}
						if ($params['TipCli']=="BURGOS"){
							if (dias_entre_fechas2($uc2fecini,$fecini)<0){
								$uc2fecini=$fecini;
							}
							if (dias_entre_fechas2($uc2fecfin,$fecfin)>0){
								$uc2fecfin=$fecfin;
							}
						}
						$uc2diastopesr=$lbldiastopesr[$i];
						$uc2porcenrecargo=$lblporcenrecargo[$i];
						$uc2diaslimitepago=$lbldiaslimitepago[$i];
						$uc2imppago=number_format($importeparcial-$uc2imppend,2,'.','');
						if (trim(''.$lbldiaprincipioalta[$i])!='')
						$uc2diasprincipioalta=$lbldiaprincipioalta[$i];
						else
						$uc2diasprincipioalta=NULL;
						
						if (trim(''.$lbldialimiterenov[$i])!='')
						$uc2diaslimiterenov=$lbldialimiterenov[$i];
						else
						$uc2diaslimiterenov=NULL;
						
						if (trim(''.$lbldialimitealta[$i])!='')
						$uc2diaslimitealta=$lbldialimitealta[$i];
						else
						$uc2diaslimitealta=NULL;
						
						$uc2idcaja=$idcaja;
						$uc2numrec=$numrec;
						if ($tipopago==10){
							$uc2imppend=0;
							$importeparcial=0;
						}
						if ($checkper>$i || $tipopago==10){
							$fecfinalta=$uc2fecfin;
							$sql="INSERT INTO USUARCUR2 (esfechalibre,ClaveUsuarCur, NumAbo, CodCur, FecGen, SitPag, ";
							$sql.=" ImpPend, FecCobro, FecIni, FecFin, DiasTopeSR, PorcenRecargo, ";
							$sql.=" DiasLimitePago, ImpPag, IDCaja, NumRec, CodRed, DiaPrincipioAlta, ";
							$sql.=" DiaLimiteAlta, DiaLimiteRenov";
							if ($_SESSION['tipc']!=="B")
								$sql.=",ClaveOmesaCursosTas";
							$sql.=") VALUES ('".$curso1dia."', ";
							$sql.="'".$uc2claveusuarcur."', ";
							$sql.="'".$uc2numabo."', ";
							$sql.="'".$uc2codcur."', ";
							$sql.="'".adaptaCon('Fec',$uc2fecgen,3)."', ";
							if ($tipopago==1 || $tipopago==4 ||$tipopago==5||$tipopago==8||$tipopago==10 || $tipopago==9)
							$sql.="'AA', ";
							else{
								if ($tipopago=='C'){
									$sql.="' C', ";
								}
								else
									$sql.="'".$uc2sitpag."', ";
							}
							if ($tipopago==1 || $tipopago==4 ||$tipopago==5||$tipopago==8||$tipopago==10 || $tipopago==9)
							$sql.="'0', ";
							else
							$sql.="'".$uc2imppend."', ";
							if ($tipopago==1 || $tipopago==4 ||$tipopago==5||$tipopago==8||$tipopago==10 || $tipopago==9)
							$sql.="'".adaptaCon('Fec',$uc2fecgen,3)."', ";
							else
							$sql.="NULL, ";
							
							$sql.="'".adaptaCon('Fec',$uc2fecini,3)."', ";
							$sql.="'".adaptaCon('Fec',$uc2fecfin,3)."', '";
							$sql.=$uc2diastopesr."', '";
							$sql.=$uc2porcenrecargo."', ";
							$sql.="'".$uc2diaslimitepago."', ";
							if ($tipopago==1 || $tipopago==4 ||$tipopago==5||$tipopago==8||$tipopago==10 || $tipopago==9)
							$sql.="'".$importeparcial."', ";
							else
							$sql.="'".$uc2imppago."', ";
							$sql.="'".$uc2idcaja."', ";
							$sql.="'".$uc2numrec."', ";
							$sql.="'".$reducusada."', ";
						}
						else{
							
							$sql="INSERT INTO USUARCUR2 (esfechalibre, ClaveUsuarCur, NumAbo, CodCur, FecGen, SitPag, ";
							$sql.=" ImpPend, FecCobro, FecIni, FecFin, DiasTopeSR, PorcenRecargo, ";
							$sql.=" DiasLimitePago, ImpPag, IDCaja, NumRec, CodRed, DiaPrincipioAlta, ";
							$sql.=" DiaLimiteAlta, DiaLimiteRenov";
							if ($_SESSION['tipc']!=="B")
								$sql.=",ClaveOmesaCursosTas";
							$sql.=") VALUES ('".$curso1dia."', ";
							$sql.="'".$uc2claveusuarcur."', ";
							$sql.="'".$uc2numabo."', ";
							$sql.="'".$uc2codcur."', ";
							$sql.="'".adaptaCon('Fec',$uc2fecgen,3)."', ";
							if ($limitealta==1)
								$sql.="'BA', ";
							else{
								if ($tipopago=='C'){
									$sql.="' C', ";
								}
								else
									$sql.="'".$uc2sitpag."', ";
							}
								
							$sql.=$uc2imppend.", ";
							$sql.="NULL, ";
							$sql.="'".adaptaCon('Fec',$uc2fecini,3)."', ";
							$sql.="'".adaptaCon('Fec',$uc2fecfin,3)."', '";
							$sql.=$uc2diastopesr."', '";
							$sql.=$uc2porcenrecargo."', ";
							$sql.="'".$uc2diaslimitepago."', '";
							$sql.=$uc2imppago."', ";
							$sql.="'', ";
							$sql.="'".$uc2numrec."', ";
							$sql.="'".$reducusada."', ";
							
						}
						
						if ($uc2diasprincipioalta==NULL)
						$sql.="NULL, ";
						else
						$sql.="'".adaptaCon('Fec',$uc2diasprincipioalta,3)."', ";
						
						if ($uc2diaslimitealta==NULL)
						$sql.="NULL, ";
						else
						$sql.="'".adaptaCon('Fec',$uc2diaslimitealta,3)."', ";
						
						if ($uc2diaslimiterenov==NULL)
						$sql.="NULL";
						else
						$sql.="'".adaptaCon('Fec',$uc2diaslimiterenov,3)."'";
						if ($_SESSION['tipc']!=="B"){
							$sql.=",'".$claveomesatasa."'";
						}
						$sql.=")";
						
						$rec=bd::ejecuta($con,$sql);
						
						
						
						
						$auxlimitepago=$lbldiaslimitepago[$i];
						if (dias_entre_fechas($fecinipago, $fechahoy)>0) {
							$frec=$fechahoy;
							$fechaaux=$fechahoy;
							if ($auxlimitepago > 0){
								while ($auxlimitepago !=0){
									$fechaaux=DiaSig($fechaaux);
									$auxlimitepago--;
								}
								
							}
							else
							if ($auxlimitepago < 0){
								while ($auxlimitepago !=0){
									$fechaaux=DiaAnt($fechaaux);
									$auxlimitepago++;
								}
							}
							$flimp=adodb_date('d/m/Y',dttm2unixdate($fechaaux));
						}
						else {
							
							$frec=$fecinipago;
							$fechaaux=$fecinipago;
							if ($auxlimitepago > 0){
								while ($auxlimitepago !=0){
									$fechaaux=DiaSig($fechaaux);
									$auxlimitepago--;
								}
								
							}
							else
							if ($auxlimitepago <0){
								while ($auxlimitepago !=0){
									$fechaaux=DiaAnt($fechaaux);
									$auxlimitepago++;
								}
							}
							$flimp=adodb_date('d/m/Y',dttm2unixdate($fechaaux));
						}
						$rest='0';
						if ($totaljugadores>1 && $params['TipCli']=="JOLASETA_FUTURAMODIFICACION")
						$rpendiente=number_format($importeparcial/$totaljugadores,2,'.','');
						else
						$rpendiente=$importeparcial;
						$rcobrado=0;
						$rest='1';
						if (($checkper>$i || $tipopago==10) && ($tipopago==1 || $tipopago==4 ||$tipopago==5 || $tipopago==8||$tipopago==10 || $tipopago==9))
						$rest='1';
						else
						$rest='0';
						
						$rmodpago=$pago;
						if ($primerrecibogenerado==''){
							$primerrecibogenerado=$numrec;
						}
						if ($pagdom=="S")
						$rmodpago='2';
						else
						$rmodpago='1';
						
						
						if ($params['TipCli']=='BILBAO' || $params['TipCli']=='BASAURI' || $params['TipCli']=='DURANGO'){
							if (($tipopago==1 || $tipopago==4 ||$tipopago==5 || $tipopago==8||$tipopago==10 || $tipopago==9))
							$rmodpago='1';
							else
							$rmodpago='2';					
						}
						if ($params['TipCli']=='JOLASETA'){
							if ($pago==2)
							$rmodpago='2';
							else
							$rmodpago='1';
							
						}
						if ($params['TipCli']=='CDEPORTIVO'){
							if ($checkper>$i || $tipopago==10){
								if ($pagdom=='S')
								$rmodpago='2';
								else
								$rmodpago='1';
							}
							else
							$rmodpago='1';
						}
						
						if ($emirec==1)
						$rmodpago='1';
						if ($tipopago=='C'){
							$rest='C';
						}
						if (dias_entre_fechas($uc2fecfin,$flimp)>0){
							$flimp=$uc2fecfin;
						}
						$sql="INSERT INTO RECIBAN (NumRec, CodRef2Usu, FecRec, FechLimPago, Est, CodUsu, ApeNom,Cif, Tit, Ent, Ofi,DigCon, Cue,paisIBAN,digconIBAN,ImpRec, Con, Cla , Ordenante, Origen, ModPago, Pendiente, Cobrado,claveagru,Observaciones,tipousu,facturado,numabofac,imprecori,serfac,eslineafac)";
						$sql.="VALUES (";
						$sql2="'".$numrec."', ";
						$sql3="'".$ref2."', '".adaptaCon('Fec',$frec,3)."', '".adaptaCon('Fec',$flimp,3)."',";
						if ($limitealta==1 && $checkper<=$i){
							$sql3.="'9',";
						}
						else
							$sql3.="'".$rest."',";
						$cadenausu="'".$micodpers."', '".substr($apellidos.", ".$nomabo, 0, 30)."', '".$cifabo."', '".$titabo."', '".$datosusu['ent']."', '".$datosusu['suc']."', '".$datosusu['digcon']."', '".$datosusu['cue']."', '".$datosusu['paisIBAN']."','".$datosusu['digconIBAN']."',";
						
						$sql4=$rpendiente.", 'CURSO ".trim($micodcur)."', '".$micodcur."', '".$ordenante."', '0', '".$rmodpago."', ";
						$cadenarecibo='';
						if (($checkper>$i || $tipopago==10) && ($tipopago==1 || $tipopago==4 ||$tipopago==5 || $tipopago==8|| $tipopago==10 || $tipopago==9)){
							if ($params['Contabilidad']==1 || ($params['Contabilidad']==2 && ($tipopago==1 || $tipopago==8 || $tipopago==9)))
								$recibosconta[$numrec]=$rpendiente;
							$sql4.="'0',";
							$sql4.=$rpendiente.','.$cagru;
							$tcadrec=2;
							$cadenarecibo=obsticket(1,$tipopago);
							
							$recibos[$contarecibos]=$numrec;
							$contarecibos++;
						}
						else{
							if ($claban=="")
							$claban=$numrec;
							$sql4.="'".$rpendiente."', ";
							$sql4.="'".$rcobrado."',";
							if ($checkper>$i || $tipopago==10)
								$sql4.="'".$cagru."'";
							else{
								if ($limitealta==1)
									$sql4.="'9'";
								else
									$sql4.="'0'";
							}
							$tcadrec=0;
							$cadenarecibo=obsticket(1,2);
							
						}
						if ($rest=='0'){
								$recibosvb[]=$numrec.'-'.$frec.'-'.$rpendiente;
						}
						if ($limitealta==1 && $checkper<=$i){
							$cadenarecibo.=chr(13).chr(10)."BAJA CURSO. DIA:".date('d/m/Y').chr(13).chr(10)." HORA:".date('H:i').chr(13).chr(10)."OPERADOR:WEB".chr(13).chr(10);
						}
						$sql4.=",'".$cadenarecibo."','0','0','".$numabofac."','".$rpendiente."','".$confac."','1')";
						if ($checkper>$i || $tipopago==10){
							$numrecfinal=$numrec;
						}
						$rec=bd::ejecuta($con,$sql.$sql2.$sql3.$cadenausu.$sql4);
						
						if ($totaljugadores>1 && $params['TipCli']=="JOLASETA_FUTURAMODIFICACION"){
							foreach ($cadenajug as $key=>$value){
								$sqlconta="SELECT Prefijo, Contador, Sufijo FROM ESCUELASCONTADO2 WHERE Clave = '4'";
								$recb=bd::ejecuta($conb,$sqlconta);
								$sera=bd::result($recb,'Prefijo');
								$serb=bd::result($recb,'Sufijo');
								$numconta=bd::result($recb,'Contador')+1;
								$numreca=$sera.transformacontador($numconta,6-strlen($sera)-strlen($serb)).$serb;
								$numreca=str_pad($numreca,6,' ',STR_PAD_LEFT);
								$sql2="'".$numreca."', ";
								$contarperiodos++;
								$sqlconta="UPDATE ESCUELASCONTADO2 SET Contador = Contador + 1 WHERE Clave = '4'";
								$recb=bd::ejecuta($conb,$sqlconta);
								$rec=bd::ejecuta($con,$sql.$sql2.$sql3.$cadenajug[$key].$sql4);
							}
						}
						
						if ($primeravuelta==''){
							
								$ref2aux=$ref2;
				
							
						}
						
						$primeravuelta=1;
						BuscaConcepto($numrec,$con,$con2);
						
						if (!isset ($recibo)){
							$recibomata=$recibomat;
							$recibo=$numrec;
							$fini=$uc2fecini;
							$ffin=$uc2fecfin;
							$dencursoaux=$dencurso;
							$sql="SELECT Den FROM HORARIOS WHERE Cod = '".$pre."'";
							$recaux=bd::ejecuta($con,$sql,0,1);//($rec,$sql)
							$preaux=traduce2('ESCUELAS-HORARIOS',trim($pre),'DEN',bd::result($recaux,'Den'));
							
							$sql="SELECT * FROM DIASSEM WHERE Cod = '".$diasem."'";
							
							$recaux=bd::ejecuta($con,$sql,0,1);//($rec,$sql)
							$diasemaux=traduce2('ESCUELAS-DIASSEM',trim($diasem),'DEN',bd::result($recaux,'Den'));
						}
						
						
						
					}
				}
			}
			else{
				
				$importeparcial=$importetotal;
				$numcont=$numcont+1;
				
				$numrec=$sera.transformacontador($numcont,6-strlen($sera)-strlen($serb)).$serb;
				
				$numrec=str_pad($numrec,6,' ',STR_PAD_LEFT);
				$recibos[$contarecibos]=$numrec;
				$contarecibos++;
				$uc2numrec=$numrec;
				if ($tipopago==10)
					$importeparcial=0;
				$sql="INSERT INTO USUARCUR2 (esfechalibre, ClaveUsuarCur, NumAbo, CodCur, FecGen, SitPag, ";
				$sql.=" ImpPend, FecCobro, FecIni, FecFin, DiasTopeSR, PorcenRecargo, ";
				$sql.=" DiasLimitePago, ImpPag, IDCaja, NumRec, CodRed, DiaPrincipioAlta, ";
				$sql.=" DiaLimiteAlta, DiaLimiteRenov) VALUES (".$curso1dia.", ";
				$sql.="'".$claveomesausuarcur."', ";
				$sql.="'".$uc2numabo."', ";
				$sql.="'".$uc2codcur."', ";
				$sql.="'".adaptaCon('Fec',$uc2fecgen,3)."', ";
				if ($tipopago==1 || $tipopago==4 ||$tipopago==5 || $tipopago==8 || $tipopago==10 || $tipopago==9)
				$sql.="'AA', ";
				else
				$sql.="'".$uc2sitpag."', ";
				if ($tipopago==1 || $tipopago==4 ||$tipopago==5 || $tipopago==8 || $tipopago==10 || $tipopago==9)
				$sql.="'0', ";
				else
				$sql.=$importeparcial.", ";
				if ($tipopago==1 || $tipopago==4 ||$tipopago==5 || $tipopago==8||$tipopago==10 || $tipopago==9)
				$sql.="'".adaptaCon('Fec',$uc2fecgen,3)."', ";
				else
				$sql.="NULL, ";
				
				$sql.="'".adaptaCon('Fec',$uc2fecini,3)."', ";
				$sql.="'".adaptaCon('Fec',$fmax,3)."', '";
				$sql.=$uc2diastopesr."', '";
				$sql.=$uc2porcenrecargo."', ";
				$sql.="'".$uc2diaslimitepago."', '";
				if ($tipopago==1 || $tipopago==4 ||$tipopago==5 || $tipopago==8||$tipopago==10 || $tipopago==9)
				$sql.=$importeparcial."', ";
				else
				$sql.="0', ";
				$sql.="'".$uc2idcaja."', ";
				$sql.="'".$uc2numrec."', ";
				$sql.="'".$reducusada."', ";
				$sql.="NULL, ";
				$sql.="NULL, ";
				$sql.="NULL";
				$sql.=")";
				
				$rec=bd::ejecuta($con,$sql);
				$recibos[$contarecibos]=$numrec;
				$contarecibos++;
				$sql="INSERT INTO RECIBAN (NumRec, CodRef2Usu, FecRec, FechLimPago, Est, CodUsu, ApeNom, ";
				$sql.="Cif, Tit, Ent, Ofi, DigCon, Cue, ImpRec, Con, Cla, Ordenante, Origen, ";
				$sql.="ModPago, Pendiente, Cobrado,Observaciones,tipousu,paisIBAN,digconIBAN,refer,facturado,numabofac,imprecori,serfac,eslineafac) ";
				$sql.="VALUES (";
				$sql.="'".$numrec."', '', ";
				$auxlimitepago=0;
				if (dias_entre_fechas($fecinipago, $fechahoy)>0) {
					$sql.="'".adaptaCon('Fec',$fechahoy,3)."', ";
					$fechaaux=$fechahoy;
					if ($auxlimitepago > 0){
						while ($auxlimitepago !=0){
							$fechaaux=DiaSig($fechaaux);
							$auxlimitepago--;
						}
						
					}
					else
					if ($auxlimitepago < 0){
						while ($auxlimitepago !=0){
							$fechaaux=DiaAnt($fechaaux);
							$auxlimitepago++;
						}
					}
					if (dias_entre_fechas($fmax,$fechaaux)>0){
							$fechaaux=$fmax;
						}
					$sql.="'".adaptaCon('Fec',adodb_date('d/m/Y',dttm2unixdate($fechaaux)),3)."', ";
				}
				else {
					
					$sql.="'".adaptaCon('Fec',$fecinipago,3)."', ";
					$fechaaux=$fecinipago;
					if ($auxlimitepago > 0){
						while ($auxlimitepago !=0){
							$fechaaux=DiaSig($fechaaux);
							$auxlimitepago--;
						}
						
					}
					else
					if ($auxlimitepago <0){
						while ($auxlimitepago !=0){
							$fechaaux=DiaAnt($fechaaux);
							$auxlimitepago++;
						}
					}
					if (dias_entre_fechas($fmax,$fechaaux)>0){
							$fechaaux=$fmax;
						}
					$sql.="'".adaptaCon('Fec',adodb_date('d/m/Y',dttm2unixdate($fechaaux)),3)."', ";
				}
				if ($primerrecibogenerado=="")
				$primerrecibogenerado=$numrec;
				
				
				$rmodpago=$pago;
				if ($pagdom=='S')
				$rmodpago='2';
				else
				$rmodpago='1';
				if ($params['TipCli']=='BILBAO' || $params['TipCli']=='BASAURI' ||$params['TipCli']=="DURANGO" || $params['TipCli']=='JOLASETA') {
					if ($pago!=2)
					$rmodpago='1';
				}
				if ($emirec==true)
				$rmodpago=1;
				
				
				$rest='0';
				$rpendiente=$importeparcial;
				$rcobrado=0;
				if ($tipopago==10){
					$rpendiente=0;
					$rcobrado=0;
				}
				if ($tipopago==1 || $tipopago==4 ||$tipopago==5 || $tipopago==8||$tipopago==10 || $tipopago==9){
					$sql.="'1', ";	
				}
				else{
					
					$recibosvb[]=$numrec.'-'.$fechaaux.'-'.$rpendiente;
					
					$sql.="'".$rest."', ";
				}
				$recibosconta[$numrec]=$rpendiente;
				$sql.="'".$micodpers."', ";
				$sql.="'".substr($apellidos.", ".$nomabo, 0, 30)."', ";
				$sql.="'".$cifabo."', ";
				$sql.="'".$titabo."', ";
				$sql.="'".$datosusu['ent']."', ";
				$sql.="'".$datosusu['suc']."', ";
				$sql.="'".$datosusu['digcon']."', ";
				$sql.="'".$datosusu['cue']."', '";
				$sql.=$importeparcial."', ";
				$sql.="'".$destipent."', ";
				$sql.="'".$micodcur."', ";
				$sql.="'".$ordenante."', ";
				$sql.="'1', ";
				$sql.="'".$rmodpago."', ";
				if ($tipopago==1 || $tipopago==4 ||$tipopago==5 || $tipopago==8||$tipopago==10 || $tipopago==9){
					$sql.="'".$rcobrado."', '";
					$sql.=$rpendiente."'";
					$tcadrec=2;
				}
				else{
					$sql.="'".$rpendiente."', '";
					$sql.=$rcobrado."'";
					$tcadrec=0;
				}
				$cadenarecibo=obsticket(1,$tipopago);
				$sql.=",'".$cadenarecibo."','0','".$datosusu['paisIBAN']."','".$datosusu['digconIBAN']."','".$idcaja."','0','".$numabofac."',".$rpendiente.",'".$confac."','1')";
				
				$rec=bd::ejecuta($con,$sql);
				$recibomata="";
		
				BuscaConcepto($numrec,$con,$con2,$entradas2,$codiva);
				
				$recibo=$numrec;
				$fini=$uc2fecini;
				$ffin=$uc2fecfin;
				$dencursoaux=$dencurso;
				$sql="SELECT Den FROM HORARIOS WHERE Cod = '".$pre."'";
				$recaux=bd::ejecuta($con,$sql);//($rec,$sql)
				$preaux=traduce2('ESCUELAS-HORARIOS',trim($pre),'DEN',bd::result($recaux,'Den'));
				
				$sql="SELECT * FROM DIASSEM WHERE Cod = '".$diasem."'";
				
				$recaux=bd::ejecuta($con,$sql);//($rec,$sql)
				$diasemaux=traduce2('ESCUELAS-DIASSEM',trim($diasem),'DEN',bd::result($recaux,'Den'));
				
			}
			//E
			//codigo de curso
			if ($_SESSION['tipc']!=="B"){
				if (is_array($recibosconta)){
					if ($params['Contabilidad']==2){
						$aux='';
						foreach ($recibosconta as $key=>$value){
							$aux.=$key.'|';
						}
						if (is_array($value))
							$imp=$value['imp'];
						else
							$imp=$value;
						generaAsiento($tipopago,$micodpers,"U",number_format($imp,2,".",""),"CU",$micodcur,$medios[1],0,$aux,0);
					
					}
					if ($params['Contabilidad']==1){
						foreach ($recibosconta as $key=>$value){
							if ($value!=''){
								if (is_array($value))
									$imp=$value['imp'];
								else
									$imp=$value;
								generaAsiento($tipopago,$micodpers,"U",number_format($imp,2,".",""),"CU",$micodcur,$medios[1],0,$key,0);
							}
						}
					}
				}
			}
			else{
				if (is_array($recibosconta)){
					if ($params['Contabilidad']==2){
						$aux='';
						foreach ($recibosconta as $key=>$value){
							$aux.=$key.'|';
						}
						if ($value!=''){
								if (is_array($value))
									$imp=$value['imp'];
								else
									$imp=$value;
							generaAsiento($tipopago,$micodpers,"U",number_format($imp,2,".",""),"E",$codtipent,$medios[1],0,$aux,0);
						}
					
					}
					if ($params['Contabilidad']==1){
						foreach ($recibosconta as $key=>$value){
							if ($value!=''){
								if (is_array($value))
									$imp=$value['imp'];
								else
									$imp=$value;
								generaAsiento($tipopago,$micodpers,"U",number_format($imp,2,".",""),"E",$codtipent,$medios[1],0,$key,0);
							}
						}
					}
				}
			}
		}
		
		if ($tipopago==4)
		actualizatarjeta($con,$micodpers,$impaux);
		$sql="UPDATE USUARCUR SET ClaBan='".$numrec."' WHERE ClaveOmesa='".$claveomesausuarcur."'";
		$rec=bd::ejecuta($con,$sql);
		
	}
		if ($params['LimiteAltaCurso']==1){
			if ($limitealta==1 && $tipc!='B'){
				$sql="UPDATE USUARCUR set motivobaja='ESPECIAL',fechabaja='".adaptaCon('Fec',adodb_date('d/m/Y',dttm2unixdate($fecfinalta)),3)."' where claveomesa='".$claveomesausuarcur."'";
				$rec=bd::ejecuta($con,$sql);
				
			}
		}
	
	
	$recibomat=$recibomata;
	
	$pre=traduce($horaux);
	$diasem=traduce($diasemaux);
	$dencurso=traduce($dencursoaux);
	if ($_SESSION['tipc']!=="B"){
		$importe=number_format($lblcobrar[0]+$importematricula+$importecarnet,2,".","");
		
		
	}
	$con7=bd::globalCon(7);

	$parametros[0]=$apellidos;
	$parametros[1]=$nomabo;
	$parametros[2]=date('d/m/Y');
	$parametros[3]=$dencurso;
	$apeclub='';
	if ($codcol!=''){
		$sql="select ape,nom from usuar where numabo='".$codcol."'";
		$rec1=bd::ejecuta($con2,$sql);
		$apeclub=bd::result($rec1,"ape");
	}
	if ($complejo!=''){
		$sql="select descrip from complejo where codigo='".$complejo."'";
		$rec1=bd::ejecuta($con2,$sql);
		$descomp=bd::result($rec1,"descrip");
	}
	
	
	$parametros[4]=$descomp;
	$parametros[5]=$preaux;
	$parametros[6]=$diasemaux;
	$parametros[7]="-FIN-";
	
	
	$param[1]=$micodpers;
	
	
	
	if ($tipopago==3){
		$tipo="alta";
		
		
		
		
		if ($papel=="C57")
		$p="";
		else
		$p="c60";
		
		/*	foreach ($recibos as $key=>$value){
		$tmp=$value;*/
		$tmp=$recibos[0];
		$tipousuario="usuario";
		$concepto=$micodcur;
		
		if ($papel=="C57")
		require "recibo2.php";
		else
		require "reciboc60.php";
		$tipticket=tipticket($tiptic);
		

		$respuesta['msg']=$ticket;
	
		
		$respuesta['codigo']=1;
		/*	}
		*/
	}
	if ($tipopago==1)
	escribeTarjeta($operacion,$referencia,$importe);
	$partic[0]=$partic[0].'-'.$partic[1];
	$partic[1]=$importe;
	$partic['usuadd']=$usuadd;

	if ($tipopago==2 || $tipopago==1 || $tipopago==4 ||$tipopago==5 || $tipopago==8|| $tipopago==10 || $tipopago==9 ||  $tipopago==11 ||  $tipopago=='C'){
		
		if ($_SESSION['tipc']!=='B')
		$tipo="alta";
		else
		$tipo='bono';
		$partic['suple']=$especial['suple'];
		
		$ticket=genTicketAlta($tipo,$numticaux,$tiptic,$tipopago,$partic,$recibos[0]);
		if ($seguro!='00')
		$ticket.=genTicketAlta($tipo,$numtic,$tiptic,$tipopago,$partic);
		
		
			$respuesta['msg']=$ticket;
		$respuesta['codigo']=1;
	}
	if ($params['TipCli']=="BILBAO")
	if ($mailabo=="")
	$respuesta['msg'].=utf8_encode('<center>'.texto('NoCorreo').'</center>');		
	
	if ($params['EncuestaCurso']==1){
		$sql="update usuarcurenc set claveusuarcur=".$claveomesausuarcur.",numabo='".$micodpers."',den='".$descurso."' where ltrim(codcur)='".trim($micodcur)."' and den='".$session_id."'";

		$rec=bd::ejecuta($con,$sql);
	}
	
	$_SESSION['lock']=1;
	limpiaBloqueoCurso($session_id,1,$claveomesa);

	regTicket("ALTA EN CURSO",$micodcur,$micodpers,$tipopago,$numticaux,$partic,$tiptic);
	
	if ($params['CursoCatUsu']==1)
		CalculaCatUsu($micodpers,'',0);
	
	if ($sor!=""){
		if ($params['TipCli']!="BILBAO")
		$sql="update citas set FechaVisit='".adaptaCon('Fec',date('d/m/Y'),3)."', HoraVisit='".adaptaCon('Hor',date('H:i'),3)."',estado='1',PtoVisit='WEB' where numabo='".$micodpers."'  and NumCita='".$sor."'";
		
		else
		$sql="update citas set FechaVisit='".adaptaCon('Fec',date('d/m/Y'),3)."', HoraVisit='".adaptaCon('Hor',date('H:i'),3)."',estado='0',PtoVisit='WEB' where numabo='".$micodpers."'  and NumCita='".$sor."'";	
		$rec=bd::ejecuta($con,$sql);
		
	}
	if (substr($params['URL'],-1,1)!='/')
	$link=$params['URL']."/imprimirticket.php?tipo=alta&cod=".$claveomesausuarcur."&semilla=".md5($claveomesausuarcur.$params['Semilla']);
	else
	$link=$params['URL']."imprimirticket.php?tipo=alta&cod=".$claveomesausuarcur."&semilla=".md5($claveomesausuarcur.$params['Semilla']);
	
	GenerarMailSms($micodpers,"","ALTA CURSO",$parametros,$con7,$con,$con2,$link,0,'',$tipemail,$tipsms);
	

	if ($logincola!=''){
	
		GenerarMailSms('',$logincola,"ALTA CURSO - COLABOR",$parametros,$con7,$con,$con2,$link,0,'',$tipemail,$tipsms);
	}	
	$sql="select Monitor from CURSOSMON where LTRIM(Cod)='".trim($micodcur)."'";
	$recaux=bd::ejecuta($con,$sql,0,1);
	if (bd::haydatos($recaux)){

		GenerarMailSms('','',"ALTA CURSO - MONITOR",$parametros,$con7,$con,$con2,$link,0,'','','',bd::result($recaux,"monitor"),9);
	}
	
	
	
	if ($justificar==1){
		$sql="select param2,numorden from ordenusu where tipoorden='SUBECOL' and param1='".substr($session_id,0,15)."'";
		$rec=bd::ejecuta($con7,$sql);
		if (bd::haydatos($rec)){
			$sql="update ordenusu set tipoorden='TOMAFOTO',param1=".$claveomesausuarcur.",param2='E21',param3='',param6='".bd::result($rec,"param2")."',estado='0' where numorden='".bd::result($rec,"numorden")."'";
			$rec=bd::ejecuta($con7,$sql);
		}
	}
	$sql="delete from ordenusu where tipoorden='SUBECOL' and param1='".substr($session_id,0,15)."'";
$rec=bd::ejecuta($con7,$sql);
	
	guardaAceptosProd($aceptos,$micodpers,$apellidos,$nomabo,'curso',$claveomesausuarcur)	;

	if ($params['AccesoDirectorio']!="0"){
		if (isset($param1)){
			$sql="select * from webloginadm where param1 like '%".CLike(trim($micodcur))."%'";
			$rec=bd::ejecuta($con7,$sql);
			if (bd::haydatos($rec)){
				$aux=explode('|',$param1);
			
				$sql="select distinct cursos.cod,cursos.des from usuarcur,cursos where cursos.cod=usuarcur.codcur and usuarcur.numabo='".$micodpers."' and usuarcur.estado<'8' ";
				$sql.=" and cursos.cod like '%".CLike(trim($aux[0]))."' ";
				$sql.="order by des";
				$rec=bd::ejecuta($con,$sql);
				if (!bd::haydatos($rec))
					$respuesta['directorio']=-1;
			}
		}
	}
	if ($params['TipCli']=='ABRA'){
		$orden="CALCBONIF";
		$param[1]=$micodpers;
		ordenUsu($orden,$param);
	}
	if ($fecalta==date('d/m/Y')){
		$orden="GENACC";
		$param[1]=$micodpers;
		ordenUsu($orden,$param);
	}
	foreach ($recibosvb as $key=>$value){
		$respuesta['reciban'].=$value.'|';
	}
	return $respuesta;		
?>
