<?php

require_once "../dep/class_util.php";
require_once "../dep/class_log.php";
require_once "../dep/class_DL_bruker.php";

$sAct = class_util::post("act");

switch ($sAct)
{
	case 'registrer':
		action_registrer();
		break;
	
	 case 'login':
		 action_login();
		 break;
    
	case 'loginDirekte':
		action_loginDirekte();
		break;
	
	case 'getUser':
		action_getUser();
		break;
	
	case 'updateUser':
		action_updateUser();
		break;
	 
	case 'changePassword':
		action_changePassword();
		break;
	
    default:
        break;
}
exit(0);

function action_registrer()
{
	$oObj = new class_DL_bruker();
	$oBruker = new data_bruker();
	
	// type registrering 0 = e-post  , 1 = mobilnummer
	$iTypeRegistrering = intval(class_util::post("typeregistrering",0));
	if ($iTypeRegistrering == 0)
		$oBruker->sEpost = class_util::post("brukernavn", "");
	else if ($iTypeRegistrering == 1)
		$oBruker->sMobil = class_util::post("brukernavn", "");
		
	// sjekk eom bruker allerede er registrert
	/*$oRes = $oObj->get_bruker_epost_mobil($oBruker);
	$aBruker = $oRes->aData;
	if (count($aBruker) > 0) // bruker finnes
	{
		$oBruker->bExists = true;
	}
	else
	{*/
		$sPassord = class_util::post("passord", "");
		$oBruker->sPassord = password_hash($sPassord, PASSWORD_DEFAULT);
		$oBruker->sBrukerType = "Bruker";
		$oBruker->bBrukerbetingelser = true;
		$oBruker->sOpprettetDato = date('Y-m-d H:i:s');
		$oBruker->bAktiv = true;

		$iBrukerId = $oObj->create($oBruker);
	//}
	echo json_encode($oBruker);
}

function action_login()
{
	//class_log::logg("DEBUG", __FILE__.' '.__LINE__);
	
	$oObj = new class_DL_bruker();
	$oBruker = new data_bruker();
	
	//class_log::logg("DEBUG", __FILE__.' '.__LINE__);
	
	$oBruker->sEpost = class_util::post("brukernavn", "");
	$oBruker->sMobil = class_util::post("brukernavn", "");
	
	$sPassord = class_util::post("passord", "");
	$sPassordHash = "";
	
	//class_log::logg("DEBUG", __FILE__.' '.__LINE__);
	
	$oRes = $oObj->get_bruker_epost_mobil($oBruker);
	$aBruker = $oRes->aData;
	if (count($aBruker) > 0)
		$sPassordHash = $aBruker[0]["Passord"];
	
	//class_log::logg("DEBUG", __FILE__.' '.__LINE__);
	
// sjekke om passord stemmer
	$aLlowLogin = false;
	if (password_verify($sPassord, $sPassordHash) && $aBruker[0]["Aktiv"]) { // sjekk eom passord er rikitg og om bruker er aktiv
		$aLlowLogin = true;
	} 
	$aBruker['allowlogin'] = $aLlowLogin;
	//class_log::logg("DEBUG", __FILE__.' '.__LINE__);
	echo json_encode($aBruker);
}

function action_loginDirekte()
{
	$oObj = new class_DL_bruker();
	$oBruker = new data_bruker();
	
	$oBruker->sEpost = class_util::post("brukernavn", "");
	$oBruker->sMobil = class_util::post("brukernavn", "");
	//$sPassord = class_util::post("passord", "");
	//$sPassordHash = "";
	
	$oRes = $oObj->get_bruker_epost_mobil($oBruker);
	$aBruker = $oRes->aData;
	if (count($aBruker) > 0)
		//$sPassordHash = $aBruker[0]["Passord"];
	
	// sjekke om passord stemmer
	$aLlowLogin = false;
	if ($aBruker[0]["Aktiv"]) { // sjekk eom passord er rikitg og om bruker er aktiv
		$aLlowLogin = true;
	} 
	$aBruker['allowlogin'] = $aLlowLogin;
	
	echo json_encode($aBruker);
}

function action_getUser()
{
	$oObj = new class_DL_bruker();
	$iBrukerId = intval(class_util::post("brukerid", 0));
	
	$oRes = $oObj->get($iBrukerId);
	$aBruker = $oRes->aData;
	
	echo json_encode($aBruker);
}

function action_updateUser() 
{
	// henter ut bruker informasjon
	$oObj = new class_DL_bruker();
	$oBruker = new data_bruker();
	
	$oBruker->iBrukerId	 = intval(class_util::post("userid", 0));
	$oBruker->sFornavn	 = class_util::post("firstname", "");
	$oBruker->sMellomnavn = class_util::post("middlename", "");
	$oBruker->sEtternavn	 = class_util::post("lastname", "");
	$oBruker->sEpost		 = class_util::post("email", "");
	$oBruker->sMobil		 = class_util::post("mobile", "");

	$oRes = $oObj->update_user($oBruker);
	$aBruker = $oRes->aData;
	
	echo json_encode($aBruker);
}

function action_changePassword()
{
	// henter ut bruker informasjon
	$oObj = new class_DL_bruker();
	$oBruker = new data_bruker();

	$iBrukerId = intval(class_util::post("userid", 0));
	$sOldPassword = class_util::post("passwordold", "");
	$sNewPassword = class_util::post("passwordnew", "");
	
	$oRes = $oObj->get_user($iBrukerId);
	$aBruker = $oRes->aData;
	if (count($aBruker) > 0)
		$sPassordHash = $aBruker[0]["Passord"];
	
	$bAllowChangePassword = false;
	// sjekke om 
	if (password_verify($sOldPassword, $sPassordHash) && $aBruker[0]["Aktiv"]) { // sjekk eom passord er rikitg og om bruker er aktiv
		$bAllowChangePassword = true;
		
		$oRes = $oObj->update_password($iBrukerId, password_hash($sNewPassword, PASSWORD_DEFAULT));
		$aBruker = $oRes->aData;
	} 
	
	echo json_encode($bAllowChangePassword);
}

