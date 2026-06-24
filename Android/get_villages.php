<?php

global $autoprefix;

$autoloader_found = false;

for ($i = 0; $i < 5; $i++) {
    $autoprefix = str_repeat('../', $i);
    if (file_exists($autoprefix.'autoloader.php')) {
        $autoloader_found = true;
        include_once $autoprefix.'autoloader.php';
        break;
    }
}

if (!$autoloader_found) {
    die('Could not find autoloading class.');
}
	include_once($autoprefix."GameEngine/config.php");
	include_once($autoprefix."GameEngine/database.php");
	include_once($autoprefix."GameEngine/Session.php");
	
	$time = time();
	
	
	$userId = $_SESSION['id_user'];
	$q = "UPDATE " . TB_PREFIX . "ndata SET viewed = '1' where uid='$userId' and viewed = '0'";
    mysqli_query($database->dblink, $q);


// BUSCA VALOR DE CHECKER
if (isset($_POST['checker']) && $_POST['checker'] == 1 && isset($session) && $session->logged_in) {
	echo $session->checker;
	exit;
}

// BUSCA VALOR DE MCHECKER
if (isset($_POST['mchecker']) && $_POST['mchecker'] == 1 && isset($session) && $session->logged_in) {
    echo $session->mchecker;
	exit;
}

// BUSCA VALOR DE MAPCHECK
if (isset($_POST['mapcheck']) && $_POST['mapcheck'] == 1 && isset($session) && $session->logged_in) {
	$wref = $_POST['wref'];
    echo substr(md5($wref), 5, 2);
	exit;
}

// BUSCA INFORMAÇÕES DO USUÁRIO
if ((isset($_POST['session_data']) && $_POST['session_data'] == 1 || isset($_GET['session_data']) && $_GET['session_data'] == 1) && isset($session) && $session-> logged_in){
	
	$responseData = [
        "uid"       => $session->uid, // Usando $session->uid que é mais comum
        "tribe"     => $session->tribe,
        "alliance"  => $session->alliance,
        "gameSpeed" => defined('SPEED') ? SPEED : 1,
		"worldMax"  => defined('WORLD_MAX') ? WORLD_MAX : 400,
		"expSpeed"  => defined('CP') ? CP : 1,
		"userCP"    => $session->cp,
        "bonus1"    => $session->bonus1,
        "bonus2"    => $session->bonus2,
        "bonus3"    => $session->bonus3,
        "bonus4"    => $session->bonus4,
		"quest"     => $session->qst,
		"questTime" => $session->qst_time,
		"readSystemMessage" => $_SESSION['ok'],
        "allianceWWs" => [] // Inicializa um array vazio para as WWs
    ];
	
	if ($session->alliance > 0) {
        $alliance_id = (int)$session->alliance; // Garante que é um inteiro para segurança

        $q = "SELECT vdat.wref, vdat.name, usr.tribe, wdat.x, wdat.y, fdat.*
                FROM " . TB_PREFIX . "fdata as fdat
                INNER JOIN " . TB_PREFIX . "vdata as vdat ON vdat.wref = fdat.vref
                INNER JOIN " . TB_PREFIX . "users as usr ON usr.id = vdat.owner
                INNER JOIN " . TB_PREFIX . "wdata as wdat ON wdat.id = vdat.wref
                WHERE usr.alliance = " . $alliance_id . " AND fdat.f99t > 0";

		$result = mysqli_query($database->dblink, $q);

        while ($row = mysqli_fetch_assoc($result)) {
			$ww_vref = (int)$row['wref'];

			$residence_level = 0;
			for ($i = 19; $i <= 39; $i++) { // Slots de edifícios internos
				if (isset($row['f'.$i.'t'])) {
					$building_type = (int)$row['f'.$i.'t'];
					if ($building_type == 25 || $building_type == 26) { // 25=Residência, 26=Palácio
						$residence_level = (int)$row['f'.$i];
						break; // Encontramos, podemos parar o loop
					}
				}
			}

            $ww_info = [
				"wwVillageId"   => $ww_vref,
				"wwVillageName" => $row['name'],
				"wwLevel"       => (int)$row['f99'],
				"wwCoordX"      => $row['x'],
				"wwCoordY"      => $row['y'],
				"wwTribe"       => (int)$row['tribe'],
				"wallLevel"     => (int)$row['f40'], // Nível da muralha (slot 40)
				"residenceLevel"=> $residence_level
			];

			$total_troops = array_fill(0, 90, 0); // Cria um array com 50 posições, todas com valor 0

            // 1. Busca tropas nativas da vila (tabela units)
            $q_units = "SELECT * FROM " . TB_PREFIX . "units WHERE vref = " . $ww_vref;
            $res_units = mysqli_query($database->dblink, $q_units);
            if ($row_units = mysqli_fetch_assoc($res_units)) {
                for ($i = 1; $i <= 90; $i++) {
                    $total_troops[$i-1] += (int)$row_units['u'.$i];
                }
            }

            // 2. Busca e SOMA todas as tropas de reforço (tabela enforcement)
            // Usamos SUM() no SQL para eficiência, ele retorna uma única linha com a soma de cada coluna.
            $sum_cols = [];
            for ($i = 1; $i <= 90; $i++) {
                $sum_cols[] = "SUM(u".$i.") as u".$i;
            }
            $q_enforcement = "SELECT " . implode(', ', $sum_cols) . " FROM " . TB_PREFIX . "enforcement WHERE vref = " . $ww_vref;
            $res_enforcement = mysqli_query($database->dblink, $q_enforcement);
            if ($row_enforcement = mysqli_fetch_assoc($res_enforcement)) {
                for ($i = 1; $i <= 90; $i++) {
                    // Adiciona a soma dos reforços ao total
                    $total_troops[$i-1] += (int)$row_enforcement['u'.$i];
                }
            }

            // 3. Adiciona o array de tropas ao objeto ww_info
            $ww_info['troops'] = $total_troops;

            $responseData['allianceWWs'][] = $ww_info; // Adiciona o objeto WW ao array
        }
    }
	
	header('Content-Type: application/json');
    echo json_encode($responseData);
    exit;
}

// BUSCA LISTA DE ARTEFATOS DISPONÍVEIS NO SERVIDOR (owner = 3 = Natars)
if (isset($_POST['artefacts']) && $_POST['artefacts'] == 1 && isset($session) && $session->logged_in) {
	$q = "SELECT art.id, art.vref, wd.x, wd.y, art.type, art.size,
	             un.u41, un.u42, un.u43, un.u44, un.u45, un.u46,
	             un.u47, un.u48, un.u49, un.u50
	      FROM " . TB_PREFIX . "artefacts art
	      LEFT JOIN " . TB_PREFIX . "wdata wd ON wd.id = art.vref
	      LEFT JOIN " . TB_PREFIX . "units  un ON un.vref = art.vref
	      WHERE art.owner = 3";

	$result = mysqli_query($database->dblink, $q);
	$list = [];
	while ($row = mysqli_fetch_assoc($result)) {
		$list[] = [
			'id'    => (int)$row['id'],
			'vref'  => (int)$row['vref'],
			'x'     => (int)$row['x'],
			'y'     => (int)$row['y'],
			'type'  => (int)$row['type'],
			'size'  => (int)$row['size'],
			'u41'   => (int)$row['u41'],
			'u42'   => (int)$row['u42'],
			'u43'   => (int)$row['u43'],
			'u44'   => (int)$row['u44'],
			'u45'   => (int)$row['u45'],
			'u46'   => (int)$row['u46'],
			'u47'   => (int)$row['u47'],
			'u48'   => (int)$row['u48'],
			'u49'   => (int)$row['u49'],
			'u50'   => (int)$row['u50'],
		];
	}

	header('Content-Type: application/json');
	echo json_encode(['artefacts' => $list, 'fetchedAt' => time()]);
	exit;
}

//BUSCA O TOTAL DE VILAS DO USUARIO
if (isset($_POST['ownerVil']) && isset($session) && $session->logged_in) {
	
	$ownerId = intval($_POST['ownerVil']);
	$response_data = ['village_count' => 0];

	if ($ownerId > 0) {
		$q = "SELECT COUNT(wref) as village_count FROM " . TB_PREFIX . "vdata WHERE owner = " . $ownerId;
		$result = mysqli_query($database->dblink, $q);
		
		if ($result) {
			$row = mysqli_fetch_assoc($result);
			$response_data['village_count'] = intval($row['village_count']);
		}
	}

	header('Content-Type: application/json');
	echo json_encode($response_data);
	exit;
	
}

// BUSCA INFORMAÇÕES BÁSICAS DE TODAS AS VILAS DO USUÁRIO
if (isset($_POST['owner']) || isset($_GET['owner']) && isset($session) && $session->logged_in) {
	
	if (isset($_POST['owner'])){
		$ownerId = intval($_POST['owner']); // É mais seguro usar prepared statements!
	}else if (isset($_GET['owner'])){
		$ownerId = intval($_GET['owner']); // É mais seguro usar prepared statements!
	}
	
    if ($ownerId > 0) {
		mysqli_query($database->dblink, "SET SESSION group_concat_max_len = 1048576");
		$mode = CP;
		$totalVillages = count($database->getProfileVillages($session->uid));
		$cpNeededNextVillage = isset(${'cp'.$mode}[$totalVillages + 1]) ? ${'cp'.$mode}[$totalVillages + 1] : null; // Use o nome correto do seu array de CP ($cpstandard?)
		$producedCPTotal = $session->cp;

        $wIdCondition = ''; // Inicializa a condição WHERE para wId
        if (isset($_POST['wId'])) {
            $wId = intval($_POST['wId']); // É mais seguro usar prepared statements!
            if($wId > 0){
                 // Adiciona condição para buscar apenas uma vila específica
                 $wIdCondition = "AND vdat.wref = '$wId'";
            }
        }
		
		$time = time();

        $q = "SELECT
			user.id, user.username, user.tribe, user.gold,
            vdat.wref, vdat.name, vdat.pop, vdat.capital, vdat.cp, vdat.loyalty, vdat.exp1, vdat.exp2, vdat.exp3, vdat.created,
			vdat.wood, vdat.clay, vdat.iron, vdat.crop, vdat.maxstore, vdat.maxcrop,
			vdat.celebration, vdat.type, vdat.natar,
            wdat.fieldtype, wdat.x, wdat.y,
			t_summary.training_units, t_summary.training_ammount, t_summary.training_timestamp,
			tdat.*,
			fdat.*,
			undat.*,
			abdat.*,
			(SELECT COUNT(id) FROM " . TB_PREFIX . "bdata as bdat WHERE bdat.wid = vdat.wref) as buildqueue,
			demdat.buildnumber, demdat.timetofinish,
			o_summary.oasis_ids, o_summary.oasis_types, o_summary.oasis_loyalties,
			atkTroops.*,            -- Dados agregados de ataques SAINDO
			reinfTroops.*,          -- Dados agregados de retornos
			reinfOutTroops.*,       -- Dados agregados de reforços a partir da vila
			atkAgainst.*,           -- Dados agregados de ataques CONTRA a vila
			reinfToMe.*,            -- Dados agregados de reforços PARA a vila (verificar sort_type)
			troopsOnVacation.*,     -- COLUNAS NOVAS: vac1, vac2, ..., vac30 (, vac_hero)
			troopsReinforcingHere.* -- COLUNAS NOVAS: ref1, ref2, ..., ref30 (, ref_hero)
        FROM " . TB_PREFIX . "vdata AS vdat
        INNER JOIN " . TB_PREFIX . "wdata AS wdat ON vdat.wref = wdat.id
        INNER JOIN " . TB_PREFIX . "users AS user ON vdat.owner = user.id
		INNER JOIN " . TB_PREFIX . "tdata AS tdat ON vdat.wref = tdat.vref
		INNER JOIN " . TB_PREFIX . "fdata AS fdat ON vdat.wref = fdat.vref
		INNER JOIN " . TB_PREFIX . "units AS undat ON vdat.wref = undat.vref
		INNER JOIN " . TB_PREFIX . "abdata AS abdat ON vdat.wref = abdat.vref
		LEFT JOIN " . TB_PREFIX . "demolition AS demdat ON vdat.wref = demdat.vref
		
		LEFT JOIN
			(SELECT
				trdat.vref,
				GROUP_CONCAT(trdat.unit ORDER BY trdat.vref SEPARATOR ',') AS training_units,
				GROUP_CONCAT(trdat.amt ORDER BY trdat.vref SEPARATOR ',') AS training_ammount,
				GROUP_CONCAT(trdat.timestamp ORDER BY trdat.vref SEPARATOR ',') AS training_timestamp
			 FROM
				" . TB_PREFIX . "training as trdat
			 GROUP BY
				trdat.vref
			) AS t_summary ON vdat.wref = t_summary.vref
		LEFT JOIN
			(SELECT
				conqured,
				GROUP_CONCAT(wref ORDER BY wref SEPARATOR ',') AS oasis_ids,
				GROUP_CONCAT(type ORDER BY wref SEPARATOR ',') AS oasis_types,
				GROUP_CONCAT(loyalty ORDER BY wref SEPARATOR ',') AS oasis_loyalties
			 FROM
				" . TB_PREFIX . "odata
			 GROUP BY
				conqured
			) AS o_summary ON vdat.wref = o_summary.conqured
		LEFT JOIN (
			SELECT
				mov.from AS vref,
				COUNT(mov.moveid) AS numAtk,
				MIN(mov.endtime) AS nextAtkEnd,
				SUM(att.t1) AS ta1, SUM(att.t2) AS ta2, SUM(att.t3) AS ta3, SUM(att.t4) AS ta4, SUM(att.t5) AS ta5,
				SUM(att.t6) AS ta6, SUM(att.t7) AS ta7, SUM(att.t8) AS ta8, SUM(att.t9) AS ta9, SUM(att.t10) AS ta10
			FROM " . TB_PREFIX . "movement AS mov
			INNER JOIN " . TB_PREFIX . "attacks AS att ON mov.ref = att.id -- Usando INNER JOIN explícito
			WHERE mov.proc = 0
			  AND mov.sort_type = 3
			GROUP BY mov.from -- Agrupando pela vila de origem
		) AS atkTroops ON vdat.wref = atkTroops.vref

		-- Join: Retornos para a vila (Nota: Soma t1-t10 de " . TB_PREFIX . "attacks)
		LEFT JOIN (
			SELECT
				mov.to AS vref,
				COUNT(mov.moveid) AS numReinf,
				MIN(mov.endtime) AS nextReinfEnd,
				SUM(mov.wood) AS lootwood, SUM(mov.clay) AS lootclay, SUM(mov.iron) AS lootiron, SUM(mov.crop) AS lootcrop,
				SUM(att.t1) AS tr1, SUM(att.t2) AS tr2, SUM(att.t3) AS tr3, SUM(att.t4) AS tr4, SUM(att.t5) AS tr5,
				SUM(att.t6) AS tr6, SUM(att.t7) AS tr7, SUM(att.t8) AS tr8, SUM(att.t9) AS tr9, SUM(att.t10) AS tr10
			FROM " . TB_PREFIX . "movement AS mov
			INNER JOIN " . TB_PREFIX . "attacks AS att ON mov.ref = att.id -- Usando INNER JOIN explícito
			WHERE mov.proc = 0
			  AND mov.sort_type = 4
			  AND att.attack_type <> 2
			GROUP BY mov.to -- Agrupando pela vila de destino
		) AS reinfTroops ON vdat.wref = reinfTroops.vref

		LEFT JOIN ( -- Join: Reforços sendo enviados dessa vila
			SELECT
				mov.from AS vref,
				COUNT(mov.moveid) AS numReinfOut,
				MIN(mov.endtime) AS nextReinfOutEnd,
				SUM(att.t1) AS rs1, SUM(att.t2) AS rs2, SUM(att.t3) AS rs3, SUM(att.t4) AS rs4, SUM(att.t5) AS rs5,
				SUM(att.t6) AS rs6, SUM(att.t7) AS rs7, SUM(att.t8) AS rs8, SUM(att.t9) AS rs9, SUM(att.t10) AS rs10
			FROM " . TB_PREFIX . "movement AS mov
			INNER JOIN " . TB_PREFIX . "attacks AS att ON mov.ref = att.id -- Usando INNER JOIN explícito
			WHERE mov.proc = 0
			  AND mov.sort_type = 3
			  AND att.attack_type = 2  -- Só conta reforços sendo enviados
			GROUP BY mov.from -- Agrupando pela vila de origem
		) AS reinfOutTroops ON tdat.vref = reinfOutTroops.vref

		-- Join: Ataques CONTRA a vila
		LEFT JOIN (
			SELECT
				mov.to AS vref,
				COUNT(mov.moveid) AS numAtkAg,
				COALESCE(MIN(mov.endtime), 0) AS nextAtkAgEnd,
				COALESCE(MAX(CASE WHEN vd.owner = 3 THEN 1 ELSE 0 END), 0) AS hasNatarAttack
			FROM " . TB_PREFIX . "movement AS mov
			INNER JOIN " . TB_PREFIX . "attacks AS att ON mov.ref = att.id -- Usando INNER JOIN explícito
			LEFT JOIN " . TB_PREFIX . "vdata AS vd ON mov.from = vd.wref
			WHERE mov.proc = 0
			  AND mov.sort_type = 3 -- Ataque
			  AND att.attack_type <> 2 -- Nao conta reforços de outros chegando
			GROUP BY mov.to -- Agrupando pela vila de destino
		) AS atkAgainst ON tdat.vref = atkAgainst.vref

		-- Join: Reforços PARA a vila
		LEFT JOIN (
			SELECT
				mov.to AS vref,
				COUNT(mov.moveid) AS numReinfTo,
				COALESCE(MIN(mov.endtime), 0) AS nextReinfToEnd
			FROM " . TB_PREFIX . "movement AS mov
			INNER JOIN " . TB_PREFIX . "attacks AS att ON mov.ref = att.id -- Usando INNER JOIN explícito
			WHERE mov.proc = 0
			  AND mov.sort_type = 3 -- Assumindo tipo 4 = reforços chegando
			  AND att.attack_type = 2 -- Apenas tipo 2 (reforço?) - verificar lógica
			GROUP BY mov.to -- Agrupando pela vila de destino
		) AS reinfToMe ON vdat.wref = reinfToMe.vref

		-- =====================================================
		-- NOVO JOIN 1: Tropas 'de Férias' (Originárias desta vila, reforçando outras)
		-- =====================================================
		LEFT JOIN (
			SELECT
				enf.from AS vref, -- Chave de junção: vila de origem
				SUM(enf.u1) AS vac1, SUM(enf.u2) AS vac2, SUM(enf.u3) AS vac3, SUM(enf.u4) AS vac4, SUM(enf.u5) AS vac5,
				SUM(enf.u6) AS vac6, SUM(enf.u7) AS vac7, SUM(enf.u8) AS vac8, SUM(enf.u9) AS vac9, SUM(enf.u10) AS vac10,
				SUM(enf.u11) AS vac11, SUM(enf.u12) AS vac12, SUM(enf.u13) AS vac13, SUM(enf.u14) AS vac14, SUM(enf.u15) AS vac15,
				SUM(enf.u16) AS vac16, SUM(enf.u17) AS vac17, SUM(enf.u18) AS vac18, SUM(enf.u19) AS vac19, SUM(enf.u20) AS vac20,
				SUM(enf.u21) AS vac21, SUM(enf.u22) AS vac22, SUM(enf.u23) AS vac23, SUM(enf.u24) AS vac24, SUM(enf.u25) AS vac25,
				SUM(enf.u26) AS vac26, SUM(enf.u27) AS vac27, SUM(enf.u28) AS vac28, SUM(enf.u29) AS vac29, SUM(enf.u30) AS vac30,
				SUM(enf.u51) AS vac51, SUM(enf.u52) AS vac52, SUM(enf.u53) AS vac53, SUM(enf.u54) AS vac54, SUM(enf.u55) AS vac55,
				SUM(enf.u56) AS vac56, SUM(enf.u57) AS vac57, SUM(enf.u58) AS vac58, SUM(enf.u59) AS vac59, SUM(enf.u60) AS vac60,
				SUM(enf.u61) AS vac61, SUM(enf.u62) AS vac62, SUM(enf.u63) AS vac63, SUM(enf.u64) AS vac64, SUM(enf.u65) AS vac65,
				SUM(enf.u66) AS vac66, SUM(enf.u67) AS vac67, SUM(enf.u68) AS vac68, SUM(enf.u69) AS vac69, SUM(enf.u70) AS vac70,
				SUM(enf.u71) AS vac71, SUM(enf.u72) AS vac72, SUM(enf.u73) AS vac73, SUM(enf.u74) AS vac74, SUM(enf.u75) AS vac75,
				SUM(enf.u76) AS vac76, SUM(enf.u77) AS vac77, SUM(enf.u78) AS vac78, SUM(enf.u79) AS vac79, SUM(enf.u80) AS vac80,
				SUM(enf.u81) AS vac81, SUM(enf.u82) AS vac82, SUM(enf.u83) AS vac83, SUM(enf.u84) AS vac84, SUM(enf.u85) AS vac85,
				SUM(enf.u86) AS vac86, SUM(enf.u87) AS vac87, SUM(enf.u88) AS vac88, SUM(enf.u89) AS vac89, SUM(enf.u90) AS vac90
				-- , SUM(enf.hero) AS vac_hero -- Descomente se precisar do herói
			FROM " . TB_PREFIX . "enforcement AS enf
			GROUP BY enf.from -- Agrupa para obter um único resultado para esta vila
		) AS troopsOnVacation ON vdat.wref = troopsOnVacation.vref

		-- =====================================================
		-- NOVO JOIN 2: Tropas Reforçando Esta Vila (Localizadas aqui, vindas de outras)
		-- =====================================================
		LEFT JOIN (
			SELECT
				enf.vref AS vref, -- Chave de junção: vila onde estão as tropas
				SUM(enf.u1) AS ref1, SUM(enf.u2) AS ref2, SUM(enf.u3) AS ref3, SUM(enf.u4) AS ref4, SUM(enf.u5) AS ref5,
				SUM(enf.u6) AS ref6, SUM(enf.u7) AS ref7, SUM(enf.u8) AS ref8, SUM(enf.u9) AS ref9, SUM(enf.u10) AS ref10,
				SUM(enf.u11) AS ref11, SUM(enf.u12) AS ref12, SUM(enf.u13) AS ref13, SUM(enf.u14) AS ref14, SUM(enf.u15) AS ref15,
				SUM(enf.u16) AS ref16, SUM(enf.u17) AS ref17, SUM(enf.u18) AS ref18, SUM(enf.u19) AS ref19, SUM(enf.u20) AS ref20,
				SUM(enf.u21) AS ref21, SUM(enf.u22) AS ref22, SUM(enf.u23) AS ref23, SUM(enf.u24) AS ref24, SUM(enf.u25) AS ref25,
				SUM(enf.u26) AS ref26, SUM(enf.u27) AS ref27, SUM(enf.u28) AS ref28, SUM(enf.u29) AS ref29, SUM(enf.u30) AS ref30,
				SUM(enf.u31) AS ref31, SUM(enf.u32) AS ref32, SUM(enf.u33) AS ref33, SUM(enf.u34) AS ref34, SUM(enf.u35) AS ref35,
				SUM(enf.u36) AS ref36, SUM(enf.u37) AS ref37, SUM(enf.u38) AS ref38, SUM(enf.u39) AS ref39, SUM(enf.u40) AS ref40,
				SUM(enf.u41) AS ref41, SUM(enf.u42) AS ref42, SUM(enf.u43) AS ref43, SUM(enf.u44) AS ref44, SUM(enf.u45) AS ref45,
				SUM(enf.u46) AS ref46, SUM(enf.u47) AS ref47, SUM(enf.u48) AS ref48, SUM(enf.u49) AS ref49, SUM(enf.u50) AS ref50,
				SUM(enf.u51) AS ref51, SUM(enf.u52) AS ref52, SUM(enf.u53) AS ref53, SUM(enf.u54) AS ref54, SUM(enf.u55) AS ref55,
				SUM(enf.u56) AS ref56, SUM(enf.u57) AS ref57, SUM(enf.u58) AS ref58, SUM(enf.u59) AS ref59, SUM(enf.u60) AS ref60,
				SUM(enf.u61) AS ref61, SUM(enf.u62) AS ref62, SUM(enf.u63) AS ref63, SUM(enf.u64) AS ref64, SUM(enf.u65) AS ref65,
				SUM(enf.u66) AS ref66, SUM(enf.u67) AS ref67, SUM(enf.u68) AS ref68, SUM(enf.u69) AS ref69, SUM(enf.u70) AS ref70,
				SUM(enf.u71) AS ref71, SUM(enf.u72) AS ref72, SUM(enf.u73) AS ref73, SUM(enf.u74) AS ref74, SUM(enf.u75) AS ref75,
				SUM(enf.u76) AS ref76, SUM(enf.u77) AS ref77, SUM(enf.u78) AS ref78, SUM(enf.u79) AS ref79, SUM(enf.u80) AS ref80,
				SUM(enf.u81) AS ref81, SUM(enf.u82) AS ref82, SUM(enf.u83) AS ref83, SUM(enf.u84) AS ref84, SUM(enf.u85) AS ref85,
				SUM(enf.u86) AS ref86, SUM(enf.u87) AS ref87, SUM(enf.u88) AS ref88, SUM(enf.u89) AS ref89, SUM(enf.u90) AS ref90
				-- , SUM(enf.hero) AS ref_hero -- Descomente se precisar do herói
			FROM " . TB_PREFIX . "enforcement AS enf
			GROUP BY enf.vref -- Agrupa para obter um único resultado para esta vila
		) AS troopsReinforcingHere ON vdat.wref = troopsReinforcingHere.vref
			
		WHERE vdat.owner = '$ownerId' $wIdCondition  -- Condição do wId adicionada aqui
        ORDER BY vdat.name ASC";

        $result = mysqli_query($database->dblink, $q);
		
		$q_hero = "SELECT 
						heroid, wref, unit, level, 
						points, experience, dead, health, attack, 
						defence, attackbonus, defencebonus,
						regeneration, inrevive, intraining
					FROM " . TB_PREFIX . "hero 
					WHERE uid = $ownerId";
		
		$r_hero = mysqli_query($database->dblink, $q_hero);
		$hero_row = mysqli_fetch_assoc($r_hero);

		//Processamento das vilas do usuario
		$allVillagesData = [];
		while ($row = $result->fetch_assoc()) {
			$row['cp_needed_next_village'] = $cpNeededNextVillage; // Adiciona o CP necessário para a próxima vila
			$row['cp_produced_total'] = $producedCPTotal;
			$row['oases'] = [];
			$row['training'] = [];
				
			// 3. Processa os dados dos oásis, se existirem
			if (!empty($row['oasis_ids'])) {
				$oasis_ids = explode(',', $row['oasis_ids']);
				$oasis_types = explode(',', $row['oasis_types']);
				$oasis_loyalties = explode(',', $row['oasis_loyalties']);

				// Itera sobre os oásis encontrados
				for ($i = 0; $i < count($oasis_ids); $i++) {
					// Cria um array associativo para o oásis atual
					$currentOasis = [
						'id'      => isset($oasis_ids[$i]) ? intval($oasis_ids[$i]) : null,
						'type'    => isset($oasis_types[$i]) ? intval($oasis_types[$i]) : null,
						'loyalty' => isset($oasis_loyalties[$i]) ? intval($oasis_loyalties[$i]) : null,
					];
					// Adiciona o array do oásis atual ao array 'oases' da $row
					$row['oases'][] = $currentOasis;
				}
			}
				
			unset($row['oasis_ids'], $row['oasis_types'], $row['oasis_loyalties']);
			
			// Processa os dados dos treinos, se existirem
			if (!empty($row['training_units'])) {
				$training_units = explode(',', $row['training_units']);
				$training_ammount = explode(',', $row['training_ammount']);
				$training_timestamp = explode(',', $row['training_timestamp']);

				// Itera sobre os treinos encontrados
				for ($i = 0; $i < count($training_units); $i++) {
					// Cria um array associativo para o treino atual
					$currentTraining = [
						'unit'      => isset($training_units[$i]) ? intval($training_units[$i]) : null,
						'amt'    => isset($training_ammount[$i]) ? intval($training_ammount[$i]) : null,
						'timestamp' => isset($training_timestamp[$i]) ? intval($training_timestamp[$i]) : null,
					];
					// Adiciona o array do treino atual ao array 'treinos' da $row
					$row['training'][] = $currentTraining;
				}
			}
				
			unset($row['training_units'], $row['training_ammount'], $row['training_timestamp']);

			$allVillagesData[] = $row;
		}

		// --- DETERMINAR O ESTADO E LOCALIZAÇÃO DO HERÓI ---
		$heroIsHome = false;
        $heroIsInMovement = false;
        $isReinforcing = false;
        $reinforcingWref = 0;
		$reinforcingId = 0;
        $hero_availability_timestamp = 0;

		if ($hero_row) {
            $heroVillageId = (int)$hero_row['wref'];

            // Procura pela vila do herói nos dados já buscados
            foreach ($allVillagesData as $village) {
                if ($village['wref'] == $heroVillageId) {
                    // Verifique se a coluna do herói na tabela 'units' (undat) é maior que 0
                    // ATENÇÃO: Substitua 'hero' pelo nome correto da coluna do herói (ex: 'u11') se for diferente.
                    if (isset($village['hero']) && $village['hero'] > 0) {
                        $heroIsHome = true;
                    }
                    break; // Encontramos a vila, podemos parar o loop
                }
            }
            
            // Se o herói não está em casa, vamos investigar
            if (!$heroIsHome) {
                // a. Verificar se ele está em movimento
                $hero_movements_query = "
                    SELECT mov.ref, mov.from, mov.to, mov.starttime, mov.endtime, mov.sort_type
                    FROM " . TB_PREFIX . "movement AS mov
                    INNER JOIN " . TB_PREFIX . "attacks AS att ON mov.ref = att.id
                    WHERE att.t11 = 1 AND mov.proc = 0 AND (mov.from = '$heroVillageId' OR mov.to = '$heroVillageId')
                    ORDER BY mov.endtime ASC";

                $r_hero_mov = mysqli_query($database->dblink, $hero_movements_query);

                if (mysqli_num_rows($r_hero_mov) > 0) {
                    $heroIsInMovement = true;
                    // Lógica para calcular o tempo de disponibilidade (do seu código original)
                    while ($mov = mysqli_fetch_assoc($r_hero_mov)) {
                        $eta = 0;
                        if ($mov['sort_type'] == 4) { // Retornando
                            $eta = (int)$mov['endtime'];
                        } else { // Indo
                            $travel_duration = (int)$mov['endtime'] - (int)$mov['starttime'];
                            $eta = (int)$mov['endtime'] + $travel_duration;
                        }
                        if ($eta > $hero_availability_timestamp) {
                            $hero_availability_timestamp = $eta;
                        }
                    }
                }

                // b. Se não está em casa E não está em movimento, deve estar reforçando
                if (!$heroIsInMovement) {
                    $q_hero_enf = "SELECT id, vref FROM " . TB_PREFIX . "enforcement WHERE `from` = '$heroVillageId' AND hero = 1 LIMIT 1";
                    $result_enf = mysqli_query($database->dblink, $q_hero_enf);

                    if ($enf_row = mysqli_fetch_assoc($result_enf)) {
                        $isReinforcing = true;
                        $reinforcingWref = (int)$enf_row['vref'];
						$reinforcingId = (int)$enf_row['id'];
                    }
                }
            }
        }

		$responseData = [
            'villages' => $allVillagesData, // Adiciona o array de vilas já processado
            'hero' => null
        ];

		if ($hero_row) {
            $responseData['hero'] = [
                'heroid' => (int)$hero_row['heroid'],
                'herowid' => (int)$hero_row['wref'],
                'availableTimestamp' => $hero_availability_timestamp,
                'isHome' => $heroIsHome,
                'isInMovement' => $heroIsInMovement,
                'isReinforcing' => $isReinforcing,
                'reinforcingWref' => $reinforcingWref,
				'reinforcingId' => $reinforcingId,
                'speed' => (int)$hero_row['unit'],
                'level' => (int)$hero_row['level'],
                'points' => (int)$hero_row['points'],
                'experience' => (int)$hero_row['experience'],
                'dead' => (int)$hero_row['dead'],
                'inrevive' => (int)$hero_row['inrevive'],
                'intraining' => (int)$hero_row['intraining'],
                'health' => (float)$hero_row['health'],
                'attack' => (int)$hero_row['attack'],
                'defence' => (int)$hero_row['defence'],
                'attackbonus' => (int)$hero_row['attackbonus'],
                'defencebonus' => (int)$hero_row['defencebonus'],
                'product' => (int)$hero_row['regeneration']
            ];
        }


        // Define o cabeçalho e envia o JSON
        header('Content-Type: application/json');
        echo json_encode($responseData);
        exit;
    } else {
         // Opcional: Lidar com ownerId inválido
         header('Content-Type: application/json');
         echo json_encode(['error' => 'Invalid owner ID']);
         exit;
    }
}

// BUSCA INFORMAÇÕES BÁSICAS DO HEROI DO USUÁRIO
if (isset($_POST['hero']) || isset($_GET['hero']) && isset($session) && $session->logged_in) {
	
	if (isset($_POST['hero'])){
		$ownerId = intval($_POST['hero']); // É mais seguro usar prepared statements!
	}else if (isset($_GET['hero'])){
		$ownerId = intval($_GET['hero']); // É mais seguro usar prepared statements!
	}
	
    if ($ownerId > 0) {
		mysqli_query($database->dblink, "SET SESSION group_concat_max_len = 1048576");

		$q_hero = "SELECT 
						heroid, wref, unit, level, 
						points, experience, dead, health, attack, 
						defence, attackbonus, defencebonus,
						regeneration, inrevive, intraining
					FROM " . TB_PREFIX . "hero 
					WHERE uid = $ownerId";
		
		$r_hero = mysqli_query($database->dblink, $q_hero);
		$hero_row = mysqli_fetch_assoc($r_hero);

		if ($hero_row) {

			$heroVillageId = (int)$hero_row['wref'];

			$q = "SELECT
				vdat.wref,
				undat.*
			FROM " . TB_PREFIX . "vdata AS vdat
			INNER JOIN " . TB_PREFIX . "units AS undat ON vdat.wref = undat.vref
			WHERE vdat.wref = '$heroVillageId'";

			$result = mysqli_query($database->dblink, $q);

			//Processamento das vilas do usuario
			$allVillagesData = [];
			while ($row = $result->fetch_assoc()) {
				$allVillagesData[] = $row;
			}

			// --- DETERMINAR O ESTADO E LOCALIZAÇÃO DO HERÓI ---
			$heroIsHome = false;
			$heroIsInMovement = false;
			$isReinforcing = false;
			$reinforcingWref = 0;
			$reinforcingId = 0;
			$hero_availability_timestamp = 0;

            // Procura pela vila do herói nos dados já buscados
            foreach ($allVillagesData as $village) {
                if ($village['wref'] == $heroVillageId) {
                    // Verifique se a coluna do herói na tabela 'units' (undat) é maior que 0
                    // ATENÇÃO: Substitua 'hero' pelo nome correto da coluna do herói (ex: 'u11') se for diferente.
                    if (isset($village['hero']) && $village['hero'] > 0) {
                        $heroIsHome = true;
                    }
                    break; // Encontramos a vila, podemos parar o loop
                }
            }
            
            // Se o herói não está em casa, vamos investigar
            if (!$heroIsHome) {
                // a. Verificar se ele está em movimento
                $hero_movements_query = "
                    SELECT mov.ref, mov.from, mov.to, mov.starttime, mov.endtime, mov.sort_type
                    FROM " . TB_PREFIX . "movement AS mov
                    INNER JOIN " . TB_PREFIX . "attacks AS att ON mov.ref = att.id
                    WHERE att.t11 = 1 AND mov.proc = 0 AND (mov.from = '$heroVillageId' OR mov.to = '$heroVillageId')
                    ORDER BY mov.endtime ASC";

                $r_hero_mov = mysqli_query($database->dblink, $hero_movements_query);

                if (mysqli_num_rows($r_hero_mov) > 0) {
                    $heroIsInMovement = true;
                    // Lógica para calcular o tempo de disponibilidade (do seu código original)
                    while ($mov = mysqli_fetch_assoc($r_hero_mov)) {
                        $eta = 0;
                        if ($mov['sort_type'] == 4) { // Retornando
                            $eta = (int)$mov['endtime'];
                        } else { // Indo
                            $travel_duration = (int)$mov['endtime'] - (int)$mov['starttime'];
                            $eta = (int)$mov['endtime'] + $travel_duration;
                        }
                        if ($eta > $hero_availability_timestamp) {
                            $hero_availability_timestamp = $eta;
                        }
                    }
                }

                // b. Se não está em casa E não está em movimento, deve estar reforçando
                if (!$heroIsInMovement) {
                    $q_hero_enf = "SELECT id, vref FROM " . TB_PREFIX . "enforcement WHERE `from` = '$heroVillageId' AND hero = 1 LIMIT 1";
                    $result_enf = mysqli_query($database->dblink, $q_hero_enf);

                    if ($enf_row = mysqli_fetch_assoc($result_enf)) {
                        $isReinforcing = true;
                        $reinforcingWref = (int)$enf_row['vref'];
						$reinforcingId = (int)$enf_row['id'];
                    }
                }
            }
        }

		$responseData = [
            'hero' => null
        ];

		if ($hero_row) {
            $responseData['hero'] = [
                'heroid' => (int)$hero_row['heroid'],
                'herowid' => (int)$hero_row['wref'],
                'availableTimestamp' => $hero_availability_timestamp,
                'isHome' => $heroIsHome,
                'isInMovement' => $heroIsInMovement,
                'isReinforcing' => $isReinforcing,
                'reinforcingWref' => $reinforcingWref,
				'reinforcingId' => $reinforcingId,
                'speed' => (int)$hero_row['unit'],
                'level' => (int)$hero_row['level'],
                'points' => (int)$hero_row['points'],
                'experience' => (int)$hero_row['experience'],
                'dead' => (int)$hero_row['dead'],
                'inrevive' => (int)$hero_row['inrevive'],
                'intraining' => (int)$hero_row['intraining'],
                'health' => (float)$hero_row['health'],
                'attack' => (int)$hero_row['attack'],
                'defence' => (int)$hero_row['defence'],
                'attackbonus' => (int)$hero_row['attackbonus'],
                'defencebonus' => (int)$hero_row['defencebonus'],
                'product' => (int)$hero_row['regeneration']
            ];
        }


        // Define o cabeçalho e envia o JSON
        header('Content-Type: application/json');
        echo json_encode($responseData);
        exit;
    } else {
         // Opcional: Lidar com ownerId inválido
         header('Content-Type: application/json');
         echo json_encode(['error' => 'Invalid owner ID']);
         exit;
    }
}

// BUSCA ALVOS PARA ATACAR
if (isset($_POST['vilId'], $_POST['x'], $_POST['y']) && isset($session) && $session->logged_in) {
	$vilId = intval($_POST['vilId']);
    $vilInfox = intval($_POST['x']);
    $vilInfoy = intval($_POST['y']);
	
	if (isset($_POST['oasis']) && $_POST['oasis'] == 1) { //BUSCA OASIS
		
		$q = "SELECT wdat.x, wdat.y, SQRT(POW(x - $vilInfox, 2) + POW(y - $vilInfoy, 2)) as distance 
			  FROM " . TB_PREFIX . "wdata as wdat
			  INNER JOIN " . TB_PREFIX . "odata as odat ON odat.wref=wdat.id
			  WHERE wdat.oasistype != 0 AND odat.conqured = 0 
			  ORDER BY distance ASC 
			  LIMIT 2";
			  
		$result = $database->query_return($q);
		echo json_encode($result);
		exit;

	}else{ //BUSCA VILAS PARA FARM
	
		define('DISTANCE_THRESHOLD_FARM', 9.0);
		$distance = intval($_POST['distance']);
		$tipoVila = $_POST['tipoVila'];
		$maxDistance = isset($_POST['distance']) ? intval($_POST['distance']) + 50 : 50;

		$limit = 15;
		$newLimit = intval($_POST['limit']);
		if ($newLimit > $limit){
			$limit = $newLimit;
		}
		
		if ($vilId > 0) {
			// 1. Descobrir o ID do dono (owner) da vila atual
			$owner_id = $session->uid;
			$owner_allyid = $session->alliance;
			$strAly = '';
			if ($owner_allyid > 0){
				$strAly = 'AND users.alliance != ' . $owner_allyid;
			}
			
			if ($owner_id <= 0) {
				header('Content-Type: application/json');
				echo json_encode(['error' => 'Dono da vila não encontrado']);
				//echo json_encode(['error' => 'Dono da vila não encontrado', 'query_owner' => $q_owner]);
				exit;
			}
				
			// 2. Query principal modificada com lógica de distância
			// Pré-calcula a fórmula da distância para clareza e evitar repetição
			$distance_calculation_sql = "SQRT(POW((" . $vilInfox . " - wdat.x), 2) + POW((" . $vilInfoy . " - wdat.y), 2))";

			$q = "SELECT 
					vdat.wref, vdat.name, wdat.x, wdat.y, users.username,
					(vdat.wood + vdat.clay + vdat.iron + vdat.crop) as recursos,
					" . $distance_calculation_sql . " as dist
				
				FROM 
					" . TB_PREFIX . "wdata AS wdat 
					STRAIGHT_JOIN " . TB_PREFIX . "vdata AS vdat ON vdat.wref = wdat.id
					STRAIGHT_JOIN " . TB_PREFIX . "users AS users ON vdat.owner = users.id
					
					-- O LEFT JOIN agora usa uma 'lista negra' muito mais inteligente
					LEFT JOIN (
						-- Condição 1: Alvos sendo atacados PELA SUA ALIANÇA
						SELECT DISTINCT m.to AS target_wref
						FROM 
							" . TB_PREFIX . "movement m 
						JOIN " . TB_PREFIX . "vdata v_attacker ON m.from = v_attacker.wref
						JOIN " . TB_PREFIX . "users u_attacker ON v_attacker.owner = u_attacker.id
						WHERE 
							u_attacker.alliance = " . (int)$owner_allyid . "
							AND m.proc = 0
							AND m.sort_type = 3
						
						UNION
						
						-- Condição 2: Alvos com ataques chegando DE QUALQUER JOGADOR
						SELECT `to` AS target_wref FROM " . TB_PREFIX . "movement WHERE proc = 0 AND sort_type = 3
						
						UNION
						
						-- Condição 3: Alvos que acabaram de ser atacados (tropas retornando DELES)
						SELECT `from` AS target_wref FROM " . TB_PREFIX . "movement WHERE proc = 0 AND sort_type = 4

					) AS blacklist ON vdat.wref = blacklist.target_wref

				WHERE
					-- Filtro do Bounding Box
					wdat.x BETWEEN " . ($vilInfox - $maxDistance) . " AND " . ($vilInfox + $maxDistance) . "
					AND wdat.y BETWEEN " . ($vilInfoy - $maxDistance) . " AND " . ($vilInfoy + $maxDistance) . "
					
					-- A verificação principal: só queremos alvos que NÃO estão na lista negra
					AND blacklist.target_wref IS NULL
					
					-- Seus outros filtros...
					AND (vdat.wood + vdat.clay + vdat.iron + vdat.crop) > 2200
					AND " . $distance_calculation_sql . " > " . $distance . "
					AND vdat.owner != " . $owner_id . "
					AND vdat.owner > 5
					AND users.username not like 'fer10fer'
					AND users.username not like 'fer10ferbr'
					AND users.username not like 'fernandomed10'
					AND vdat.pop > 250
					" . $strAly . "
					
				ORDER BY 
					dist ASC
				LIMIT 
					" . $limit;

			$result = mysqli_query($database->dblink, $q);

			$unatVilas = [];
			while ($row = $result->fetch_assoc()) {
				$unatVilas[] = $row;
			}

			header('Content-Type: application/json');
			echo json_encode($unatVilas);
			exit;
		}
    }
}

// BUSCA INFORMAÇÕES E ESTADO ATUAL DAS TROPAS DA VILA
if ((isset($_POST['vref']) && isset($_POST['tropas']) || isset($_GET['vref']) && isset($_GET['tropas'])) && isset($session) && $session->logged_in){
	
	mysqli_query($database->dblink, "SET SESSION group_concat_max_len = 1048576");
	if (isset($_GET['vref'])){
		$vref = intval($_GET['vref']);
	}else if (isset($_POST['vref'])){
		$vref = intval($_POST['vref']);
	}
	
	$q = "SELECT
			t_summary.training_units, t_summary.training_ammount, t_summary.training_timestamp,
			r_summary.research_techs, r_summary.research_timestamp,
			tdat.*,
			undat.*,
			wounded.*,		  		-- Dados de tropas feridas
			abdat.*,
			atkTroops.*,            -- Dados agregados de ataques SAINDO
			reinfTroops.*,          -- Dados agregados de retornos
			reinfOutTroops.*,       -- Dados agregados de reforços a partir da vila
			atkAgainst.*,           -- Dados agregados de ataques CONTRA a vila
			reinfToMe.*,            -- Dados agregados de reforços PARA a vila
			troopsReinforcingHere.* -- COLUNAS NOVAS: ref1, ref2, ..., ref30 (, ref_hero)
        FROM " . TB_PREFIX . "tdata AS tdat
		INNER JOIN " . TB_PREFIX . "units AS undat ON tdat.vref = undat.vref
		INNER JOIN " . TB_PREFIX . "abdata AS abdat ON tdat.vref = abdat.vref
		LEFT JOIN (SELECT * FROM " . TB_PREFIX . "wounded) AS wounded ON tdat.vref = wounded.vref

		LEFT JOIN
			(SELECT
				trdat.vref,
				GROUP_CONCAT(trdat.unit ORDER BY trdat.vref SEPARATOR ',') AS training_units,
				GROUP_CONCAT(trdat.amt ORDER BY trdat.vref SEPARATOR ',') AS training_ammount,
				GROUP_CONCAT(trdat.timestamp ORDER BY trdat.vref SEPARATOR ',') AS training_timestamp
			 FROM
				" . TB_PREFIX . "training as trdat
			 GROUP BY
				trdat.vref
			) AS t_summary ON tdat.vref = t_summary.vref
		
		LEFT JOIN
			(SELECT
				resdat.vref,
				GROUP_CONCAT(resdat.tech ORDER BY resdat.id SEPARATOR ',') AS research_techs,
				GROUP_CONCAT(resdat.timestamp ORDER BY resdat.id SEPARATOR ',') AS research_timestamp
			 FROM
				" . TB_PREFIX . "research as resdat
			 GROUP BY
				resdat.vref
			) AS r_summary ON tdat.vref = r_summary.vref
		
		LEFT JOIN ( -- Join: Ataques da vila
			SELECT
				mov.from AS vref,
				COUNT(mov.moveid) AS numAtk,
				MIN(mov.endtime) AS nextAtkEnd,
				SUM(att.t1) AS ta1, SUM(att.t2) AS ta2, SUM(att.t3) AS ta3, SUM(att.t4) AS ta4, SUM(att.t5) AS ta5,
				SUM(att.t6) AS ta6, SUM(att.t7) AS ta7, SUM(att.t8) AS ta8, SUM(att.t9) AS ta9, SUM(att.t10) AS ta10
			FROM " . TB_PREFIX . "movement AS mov
			INNER JOIN " . TB_PREFIX . "attacks AS att ON mov.ref = att.id -- Usando INNER JOIN explícito
			WHERE mov.proc = 0
			  AND mov.sort_type = 3
			  AND att.attack_type <> 2  -- Nao conta reforços sendo enviados
			GROUP BY mov.from -- Agrupando pela vila de origem
		) AS atkTroops ON tdat.vref = atkTroops.vref
		
		LEFT JOIN ( -- Join: Retornos para a vila
			SELECT
				mov.to AS vref,
				COUNT(mov.moveid) AS numReinf,
				MIN(mov.endtime) AS nextReinfEnd,
				SUM(mov.wood) AS lootwood, SUM(mov.clay) AS lootclay, SUM(mov.iron) AS lootiron, SUM(mov.crop) AS lootcrop,
				SUM(att.t1) AS tr1, SUM(att.t2) AS tr2, SUM(att.t3) AS tr3, SUM(att.t4) AS tr4, SUM(att.t5) AS tr5,
				SUM(att.t6) AS tr6, SUM(att.t7) AS tr7, SUM(att.t8) AS tr8, SUM(att.t9) AS tr9, SUM(att.t10) AS tr10
			FROM " . TB_PREFIX . "movement AS mov
			INNER JOIN " . TB_PREFIX . "attacks AS att ON mov.ref = att.id -- Usando INNER JOIN explícito
			WHERE mov.proc = 0
			  AND mov.sort_type = 4
			  AND att.attack_type <> 2
			GROUP BY mov.to -- Agrupando pela vila de destino
		) AS reinfTroops ON tdat.vref = reinfTroops.vref

		LEFT JOIN ( -- Join: Reforços sendo enviados dessa vila
			SELECT
				mov.from AS vref,
				COUNT(mov.moveid) AS numReinfOut,
				MIN(mov.endtime) AS nextReinfOutEnd,
				SUM(att.t1) AS rs1, SUM(att.t2) AS rs2, SUM(att.t3) AS rs3, SUM(att.t4) AS rs4, SUM(att.t5) AS rs5,
				SUM(att.t6) AS rs6, SUM(att.t7) AS rs7, SUM(att.t8) AS rs8, SUM(att.t9) AS rs9, SUM(att.t10) AS rs10
			FROM " . TB_PREFIX . "movement AS mov
			INNER JOIN " . TB_PREFIX . "attacks AS att ON mov.ref = att.id -- Usando INNER JOIN explícito
			WHERE mov.proc = 0
			  AND mov.sort_type = 3
			  AND att.attack_type = 2  -- Só conta reforços sendo enviados
			GROUP BY mov.from -- Agrupando pela vila de origem
		) AS reinfOutTroops ON tdat.vref = reinfOutTroops.vref

		LEFT JOIN ( -- Join: Ataques CONTRA a vila
			SELECT
				mov.to AS vref,
				COUNT(mov.moveid) AS numAtkAg,
				COALESCE(MIN(mov.endtime), 0) AS nextAtkAgEnd,
				COALESCE(MAX(CASE WHEN vd.owner = 3 THEN 1 ELSE 0 END), 0) AS hasNatarAttack
			FROM " . TB_PREFIX . "movement AS mov
			INNER JOIN " . TB_PREFIX . "attacks AS att ON mov.ref = att.id -- Usando INNER JOIN explícito
			LEFT JOIN " . TB_PREFIX . "vdata AS vd ON mov.from = vd.wref
			WHERE mov.proc = 0
			  AND mov.sort_type = 3 -- Ataque
			  AND att.attack_type <> 2 -- Nao conta reforços de outros chegando
			GROUP BY mov.to -- Agrupando pela vila de destino
		) AS atkAgainst ON tdat.vref = atkAgainst.vref

		LEFT JOIN ( -- Join: Reforços PARA a vila
			SELECT
				mov.to AS vref,
				COUNT(mov.moveid) AS numReinfTo,
				COALESCE(MIN(mov.endtime), 0) AS nextReinfToEnd
			FROM " . TB_PREFIX . "movement AS mov
			INNER JOIN " . TB_PREFIX . "attacks AS att ON mov.ref = att.id -- Usando INNER JOIN explícito
			WHERE mov.proc = 0
			  AND mov.sort_type = 3 -- Assumindo tipo 4 = reforços chegando
			  AND att.attack_type = 2 -- Apenas reforços chegando
			GROUP BY mov.to -- Agrupando pela vila de destino
		) AS reinfToMe ON tdat.vref = reinfToMe.vref

		LEFT JOIN ( -- Join: Reforços de outras vilas nesta vila
			SELECT
				enf.vref AS vref, -- Chave de junção: vila onde estão as tropas
				SUM(enf.u1) AS ref1, SUM(enf.u2) AS ref2, SUM(enf.u3) AS ref3, SUM(enf.u4) AS ref4, SUM(enf.u5) AS ref5,
				SUM(enf.u6) AS ref6, SUM(enf.u7) AS ref7, SUM(enf.u8) AS ref8, SUM(enf.u9) AS ref9, SUM(enf.u10) AS ref10,
				SUM(enf.u11) AS ref11, SUM(enf.u12) AS ref12, SUM(enf.u13) AS ref13, SUM(enf.u14) AS ref14, SUM(enf.u15) AS ref15,
				SUM(enf.u16) AS ref16, SUM(enf.u17) AS ref17, SUM(enf.u18) AS ref18, SUM(enf.u19) AS ref19, SUM(enf.u20) AS ref20,
				SUM(enf.u21) AS ref21, SUM(enf.u22) AS ref22, SUM(enf.u23) AS ref23, SUM(enf.u24) AS ref24, SUM(enf.u25) AS ref25,
				SUM(enf.u26) AS ref26, SUM(enf.u27) AS ref27, SUM(enf.u28) AS ref28, SUM(enf.u29) AS ref29, SUM(enf.u30) AS ref30,
				SUM(enf.u31) AS ref31, SUM(enf.u32) AS ref32, SUM(enf.u33) AS ref33, SUM(enf.u34) AS ref34, SUM(enf.u35) AS ref35,
				SUM(enf.u36) AS ref36, SUM(enf.u37) AS ref37, SUM(enf.u38) AS ref38, SUM(enf.u39) AS ref39, SUM(enf.u40) AS ref40,
				SUM(enf.u41) AS ref41, SUM(enf.u42) AS ref42, SUM(enf.u43) AS ref43, SUM(enf.u44) AS ref44, SUM(enf.u45) AS ref45,
				SUM(enf.u46) AS ref46, SUM(enf.u47) AS ref47, SUM(enf.u48) AS ref48, SUM(enf.u49) AS ref49, SUM(enf.u50) AS ref50,
				SUM(enf.u51) AS ref51, SUM(enf.u52) AS ref52, SUM(enf.u53) AS ref53, SUM(enf.u54) AS ref54, SUM(enf.u55) AS ref55,
				SUM(enf.u56) AS ref56, SUM(enf.u57) AS ref57, SUM(enf.u58) AS ref58, SUM(enf.u59) AS ref59, SUM(enf.u60) AS ref60,
				SUM(enf.u61) AS ref61, SUM(enf.u62) AS ref62, SUM(enf.u63) AS ref63, SUM(enf.u64) AS ref64, SUM(enf.u65) AS ref65,
				SUM(enf.u66) AS ref66, SUM(enf.u67) AS ref67, SUM(enf.u68) AS ref68, SUM(enf.u69) AS ref69, SUM(enf.u70) AS ref70,
				SUM(enf.u71) AS ref71, SUM(enf.u72) AS ref72, SUM(enf.u73) AS ref73, SUM(enf.u74) AS ref74, SUM(enf.u75) AS ref75,
				SUM(enf.u76) AS ref76, SUM(enf.u77) AS ref77, SUM(enf.u78) AS ref78, SUM(enf.u79) AS ref79, SUM(enf.u80) AS ref80,
				SUM(enf.u81) AS ref81, SUM(enf.u82) AS ref82, SUM(enf.u83) AS ref83, SUM(enf.u84) AS ref84, SUM(enf.u85) AS ref85,
				SUM(enf.u86) AS ref86, SUM(enf.u87) AS ref87, SUM(enf.u88) AS ref88, SUM(enf.u89) AS ref89, SUM(enf.u90) AS ref90
				-- , SUM(enf.hero) AS ref_hero -- Descomente se precisar do herói
			FROM " . TB_PREFIX . "enforcement AS enf
			GROUP BY enf.vref -- Agrupa para obter um único resultado para esta vila
		) AS troopsReinforcingHere ON tdat.vref = troopsReinforcingHere.vref
			
		WHERE tdat.vref = '$vref'";
	
	$result = mysqli_query($database->dblink, $q);
	$vilas = []; // Inicializa o array de vilas

	while ($row = $result->fetch_assoc()) {
		$row['training'] = [];
		$row['research'] = [];
		
		// Processa os dados dos treinos, se existirem
		if (!empty($row['training_units'])) {
			$training_units = explode(',', $row['training_units']);
			$training_ammount = explode(',', $row['training_ammount']);
			$training_timestamp = explode(',', $row['training_timestamp']);

			// Itera sobre os treinos encontrados
			for ($i = 0; $i < count($training_units); $i++) {
				// Cria um array associativo para o treino atual
				$currentTraining = [
					'unit'      => isset($training_units[$i]) ? intval($training_units[$i]) : null,
					'amt'    => isset($training_ammount[$i]) ? intval($training_ammount[$i]) : null,
					'timestamp' => isset($training_timestamp[$i]) ? intval($training_timestamp[$i]) : null,
				];
				// Adiciona o array do treino atual ao array 'treinos' da $row
				$row['training'][] = $currentTraining;
			}
		}
			
		unset($row['training_units'], $row['training_ammount'], $row['training_timestamp']);
		
		// Processa os dados das pesquisas, se existirem
		if (!empty($row['research_techs'])) {
			$research_techs = explode(',', $row['research_techs']);
			$research_timestamp = explode(',', $row['research_timestamp']);

			// Itera sobre as pesquisas encontrados
			for ($i = 0; $i < count($research_techs); $i++) {
				// Cria um array associativo para a pesquisa atual
				$currentResearch = [
					'tech'      => isset($research_techs[$i]) ? $research_techs[$i] : null,
					'timestamp' => isset($research_timestamp[$i]) ? intval($research_timestamp[$i]) : null,
				];
				// Adiciona o array da pesquisa atual ao array 'pesquisas' da $row
				$row['research'][] = $currentResearch;
			}
		}
			
		unset($row['research_techs'], $row['research_timestamp']);
		
		$vilas[] = $row;
    }

	// Busca registros individuais de enforcement (reforços enviados para outras vilas)
	$enforcements = [];
	$tribe = (int)$session->tribe;
	$base = ($tribe - 1) * 10;
	$q_enf = "SELECT id, vref, u1, u2, u3, u4, u5, u6, u7, u8, u9, u10,
				u11, u12, u13, u14, u15, u16, u17, u18, u19, u20,
				u21, u22, u23, u24, u25, u26, u27, u28, u29, u30,
				u31, u32, u33, u34, u35, u36, u37, u38, u39, u40,
				u41, u42, u43, u44, u45, u46, u47, u48, u49, u50,
				u51, u52, u53, u54, u55, u56, u57, u58, u59, u60,
				u61, u62, u63, u64, u65, u66, u67, u68, u69, u70,
				u71, u72, u73, u74, u75, u76, u77, u78, u79, u80,
				u81, u82, u83, u84, u85, u86, u87, u88, u89, u90,
				hero
			FROM " . TB_PREFIX . "enforcement
			WHERE `from` = $vref";
	$r_enf = mysqli_query($database->dblink, $q_enf);
	while ($enf = mysqli_fetch_assoc($r_enf)) {
		$record = ['id' => (int)$enf['id'], 'vref' => (int)$enf['vref']];
		for ($i = 1; $i <= 10; $i++) {
			$record["t$i"] = (int)$enf["u" . ($base + $i)];
		}
		$enforcements[] = $record;
	}
	if (!empty($vilas)) {
		$vilas[0]['enforcements'] = $enforcements;
	}

    header('Content-Type: application/json');
    echo json_encode($vilas);
    exit;
	
}

// BUSCA INFORMAÇÕES E ESTADO ATUAL DO ESTADO DA VILA
if (isset($_POST['vref']) && isset($_POST['villageState']) || isset($_GET['vref'])){
	set_time_limit(0);
	mysqli_query($database->dblink, "SET SESSION group_concat_max_len = 1048576");
	if (isset($_POST['vref'])) $vref = intval($_POST['vref']);
	if (isset($_GET['vref'])) $vref = intval($_GET['vref']);
	
	$q = "SELECT vdat.wref, vdat.name, vdat.capital, vdat.maxstore, vdat.maxcrop, vdat.wood, vdat.clay, vdat.iron, vdat.crop,
		vdat.celebration, vdat.type, vdat.owner, vdat.natar, vdat.pop,
		ddat.*,
		fdat.*,
		wdat.x, wdat.y,
		b_summary.build_types, b_summary.build_slots, b_summary.build_levels, b_summary.build_timestamp,
		o_summary.oasis_ids, o_summary.oasis_types, o_summary.oasis_loyalties,
        
		-- Colunas do Mercado (com Nomes)
        incoming.incoming_sources, incoming.incoming_source_names, incoming.incoming_arrivals, incoming.incoming_resources, incoming.incoming_merchants,
        outgoing.outgoing_targets, outgoing.outgoing_target_names, outgoing.outgoing_arrivals, outgoing.outgoing_resources, outgoing.outgoing_merchants,
        returning.returning_target_names, returning.returning_arrivals, returning.returning_merchants,
        
		-- Colunas de artefatos
		village_artifacts.vilart_ids, village_artifacts.vilart_owners, village_artifacts.vilart_types, village_artifacts.vilart_sizes, village_artifacts.vilart_actives, village_artifacts.vilart_conquereds,
		account_artifacts.accart_owners, account_artifacts.accart_ids, account_artifacts.accart_vrefs, account_artifacts.accart_types, account_artifacts.accart_sizes, account_artifacts.accart_actives, account_artifacts.accart_conquereds
		
		FROM " . TB_PREFIX . "vdata as vdat
		INNER JOIN " . TB_PREFIX . "wdata AS wdat ON vdat.wref = wdat.id
		LEFT JOIN " . TB_PREFIX . "fdata AS fdat ON vdat.wref = fdat.vref
		LEFT JOIN " . TB_PREFIX . "demolition AS ddat ON vdat.wref = ddat.vref
		
		LEFT JOIN
			(SELECT
				conqured,
				GROUP_CONCAT(wref ORDER BY wref SEPARATOR ',') AS oasis_ids,
				GROUP_CONCAT(type ORDER BY wref SEPARATOR ',') AS oasis_types,
				GROUP_CONCAT(loyalty ORDER BY wref SEPARATOR ',') AS oasis_loyalties
			 FROM
				" . TB_PREFIX . "odata
			 GROUP BY
				conqured
			) AS o_summary ON vdat.wref = o_summary.conqured
			
		LEFT JOIN -- Build Queue
			(SELECT
				bdat.wid,
				GROUP_CONCAT(bdat.type ORDER BY bdat.id SEPARATOR ',') AS build_types,
				GROUP_CONCAT(bdat.field ORDER BY bdat.id SEPARATOR ',') AS build_slots,
				GROUP_CONCAT(bdat.level ORDER BY bdat.id SEPARATOR ',') AS build_levels,
				GROUP_CONCAT(bdat.timestamp ORDER BY bdat.id SEPARATOR ',') AS build_timestamp
			 FROM
				" . TB_PREFIX . "bdata as bdat
			 GROUP BY
				bdat.wid
			) AS b_summary ON vdat.wref = b_summary.wid

		LEFT JOIN -- MERCADORES CHEGANDO (INCOMING)
			(SELECT
				mov.to AS vref,
				GROUP_CONCAT(mov.from ORDER BY mov.endtime SEPARATOR ';') AS incoming_sources,
				GROUP_CONCAT(vdat_source.name ORDER BY mov.endtime SEPARATOR ';') AS incoming_source_names, -- NOME DA VILA DE ORIGEM
				GROUP_CONCAT(mov.endtime ORDER BY mov.endtime SEPARATOR ';') AS incoming_arrivals,
				GROUP_CONCAT(CONCAT_WS(',', snd.wood, snd.clay, snd.iron, snd.crop) ORDER BY mov.endtime SEPARATOR ';') AS incoming_resources,
				GROUP_CONCAT(snd.merchant ORDER BY mov.endtime SEPARATOR ';') AS incoming_merchants
			FROM " . TB_PREFIX . "movement AS mov 
			INNER JOIN " . TB_PREFIX . "send AS snd ON mov.ref = snd.id
			LEFT JOIN " . TB_PREFIX . "vdata AS vdat_source ON mov.from = vdat_source.wref -- JOIN PARA BUSCAR O NOME
			WHERE mov.proc = 0 AND mov.sort_type = 0
			GROUP BY mov.to
			) AS incoming ON vdat.wref = incoming.vref

		LEFT JOIN -- MERCADORES SAINDO (OUTGOING)
			(SELECT
				mov.from AS vref,
				GROUP_CONCAT(mov.to ORDER BY mov.endtime SEPARATOR ';') AS outgoing_targets,
				GROUP_CONCAT(vdat_target.name ORDER BY mov.endtime SEPARATOR ';') AS outgoing_target_names, -- NOME DA VILA DE DESTINO
				GROUP_CONCAT(mov.endtime ORDER BY mov.endtime SEPARATOR ';') AS outgoing_arrivals,
				GROUP_CONCAT(CONCAT_WS(',', snd.wood, snd.clay, snd.iron, snd.crop) ORDER BY mov.endtime SEPARATOR ';') AS outgoing_resources,
				GROUP_CONCAT(snd.merchant ORDER BY mov.endtime SEPARATOR ';') AS outgoing_merchants
			FROM " . TB_PREFIX . "movement AS mov 
			INNER JOIN " . TB_PREFIX . "send AS snd ON mov.ref = snd.id
			LEFT JOIN " . TB_PREFIX . "vdata AS vdat_target ON mov.to = vdat_target.wref -- JOIN PARA BUSCAR O NOME
			WHERE mov.proc = 0 AND mov.sort_type = 0
			GROUP BY mov.from
			) AS outgoing ON vdat.wref = outgoing.vref

		LEFT JOIN -- MERCADORES RETORNANDO (RETURNING)
			(SELECT
				mov.from AS vref,
				GROUP_CONCAT(vdat_target.name ORDER BY (mov.endtime + (mov.endtime - mov.starttime)) SEPARATOR ';') AS returning_target_names, -- NOME DA VILA VISITADA
				GROUP_CONCAT((mov.endtime + (mov.endtime - mov.starttime)) ORDER BY (mov.endtime + (mov.endtime - mov.starttime)) SEPARATOR ';') AS returning_arrivals,
				GROUP_CONCAT(snd.merchant ORDER BY (mov.endtime + (mov.endtime - mov.starttime)) SEPARATOR ';') AS returning_merchants
			FROM " . TB_PREFIX . "movement AS mov 
			INNER JOIN " . TB_PREFIX . "send AS snd ON mov.ref = snd.id
			LEFT JOIN " . TB_PREFIX . "vdata AS vdat_target ON mov.to = vdat_target.wref -- JOIN PARA BUSCAR O NOME
			WHERE mov.proc = 1 AND mov.sort_type = 0 AND (mov.endtime + (mov.endtime - mov.starttime)) > " . time() . "
			GROUP BY mov.from
			) AS returning ON vdat.wref = returning.vref
			
		LEFT JOIN -- JOIN 1: Artefatos localizados NESTA VILA ESPECÍFICA
			(SELECT
				artf.vref,
				GROUP_CONCAT(artf.id ORDER BY artf.size SEPARATOR ';') AS vilart_ids,
				GROUP_CONCAT(artf.owner ORDER BY artf.size SEPARATOR ';') AS vilart_owners,
				GROUP_CONCAT(artf.type ORDER BY artf.size SEPARATOR ';') AS vilart_types,
				GROUP_CONCAT(artf.size ORDER BY artf.size SEPARATOR ';') AS vilart_sizes,
				GROUP_CONCAT(artf.active ORDER BY artf.size SEPARATOR ';') AS vilart_actives,
				GROUP_CONCAT(artf.conquered ORDER BY artf.size SEPARATOR ';') AS vilart_conquereds
			 FROM 
				" . TB_PREFIX . "artefacts AS artf
			 GROUP BY
				artf.vref
			) AS village_artifacts ON vdat.wref = village_artifacts.vref
			
		LEFT JOIN -- JOIN 2: Artefatos que pertencem ao DONO desta vila e são de escopo de CONTA ou ÚNICO ou Planos de construção da alianca que afetam essa vila
			(SELECT
				usr.alliance,
                GROUP_CONCAT(artf.owner ORDER BY artf.size, artf.conquered SEPARATOR ';') AS accart_owners,
				GROUP_CONCAT(artf.id ORDER BY artf.size, artf.conquered SEPARATOR ';') AS accart_ids,
				GROUP_CONCAT(artf.vref ORDER BY artf.size, artf.conquered SEPARATOR ';') AS accart_vrefs,
				GROUP_CONCAT(artf.type ORDER BY artf.size, artf.conquered SEPARATOR ';') AS accart_types,
				GROUP_CONCAT(artf.size ORDER BY artf.size, artf.conquered SEPARATOR ';') AS accart_sizes,
				GROUP_CONCAT(artf.active ORDER BY artf.size, artf.conquered SEPARATOR ';') AS accart_actives,
				GROUP_CONCAT(artf.conquered ORDER BY artf.size, artf.conquered SEPARATOR ';') AS accart_conquereds
			 FROM 
				" . TB_PREFIX . "artefacts AS artf
			INNER JOIN
				" . TB_PREFIX . "users AS usr ON usr.id = artf.owner -- Adicionado JOIN com a tabela de usuários
			 WHERE
				artf.owner = $session->uid and (artf.size = 2 OR artf.size = 3 or artf.type = 11) -- Apenas artefatos de conta, únicos e planos de construção
				or (artf.type = 11 AND usr.alliance = $session->alliance AND usr.alliance != 0) -- Planos de construção de membros da aliança
			 GROUP BY
				usr.alliance
			) AS account_artifacts ON $session->alliance = account_artifacts.alliance
			
		WHERE vdat.wref='$vref'";
	
	$result = mysqli_query($database->dblink, $q);
	$vilas = []; // Inicializa o array de vilas

	while ($row = $result->fetch_assoc()) {
		$row['readSystemMessage'] = $_SESSION['ok'];
		$row['buildQueue'] = [];
		$row['market_incoming'] = [];
		$row['market_outgoing'] = [];
		$row['market_returning'] = [];
		$row['artefacts'] = [];
		$row['oases'] = [];
		
		$processed_artefact_ids = [];
		
		if (!empty($row['oasis_ids'])) {
			$oasis_ids = explode(',', $row['oasis_ids']);
			$oasis_types = explode(',', $row['oasis_types']);
			$oasis_loyalties = explode(',', $row['oasis_loyalties']);

			// Itera sobre os oásis encontrados
			for ($i = 0; $i < count($oasis_ids); $i++) {
				// Cria um array associativo para o oásis atual
				$currentOasis = [
					'id'      => isset($oasis_ids[$i]) ? intval($oasis_ids[$i]) : null,
					'type'    => isset($oasis_types[$i]) ? intval($oasis_types[$i]) : null,
					'loyalty' => isset($oasis_loyalties[$i]) ? intval($oasis_loyalties[$i]) : null,
				];
				// Adiciona o array do oásis atual ao array 'oases' da $row
				$row['oases'][] = $currentOasis;
			}
		}
			
		unset($row['oasis_ids'], $row['oasis_types'], $row['oasis_loyalties']);
		
		if (count($row['oases']) < 3){
			$row['nearby_oases'] = [];
			$x = intval($row['x']);
			$y = intval($row['y']);
			
			$q_oases = "SELECT wdat.id, wdat.oasistype, wdat.x, wdat.y 
						FROM " . TB_PREFIX . "wdata as wdat
						INNER JOIN " . TB_PREFIX . "odata as od ON od.wref = wdat.id
						WHERE wdat.oasistype > 0 
						  AND od.owner = 2 
						  AND wdat.x BETWEEN " . ($x - 3) . " AND " . ($x + 3) . " 
						  AND wdat.y BETWEEN " . ($y - 3) . " AND " . ($y + 3);

			$result_oases = mysqli_query($database->dblink, $q_oases);
						
			while ($oasis_row = $result_oases->fetch_assoc()) {
				$row['nearby_oases'][] = $oasis_row;
			}
		}
		
		if (!empty($row['build_types'])) {
			$build_types = explode(',', $row['build_types']);
			$build_slots = explode(',', $row['build_slots']);
			$build_levels = explode(',', $row['build_levels']);
			$build_timestamp = explode(',', $row['build_timestamp']);

			for ($i = 0; $i < count($build_types); $i++) {
				$currentBuild = [
					'type'      => isset($build_types[$i]) ? intval($build_types[$i]) : null,
					'field'    => isset($build_slots[$i]) ? intval($build_slots[$i]) : null,
					'level'    => isset($build_levels[$i]) ? intval($build_levels[$i]) : null,
					'timestamp' => isset($build_timestamp[$i]) ? intval($build_timestamp[$i]) : null,
				];
				$row['buildQueue'][] = $currentBuild;
			}
		}
			
		unset($row['build_types'], $row['build_slots'], $row['build_levels'], $row['build_timestamp']);
		
		// Processa os mercadores CHEGANDO
		if (!empty($row['incoming_arrivals'])) {
			$sources = explode(';', $row['incoming_sources']);
			$names = explode(';', $row['incoming_source_names']); // Novo
			$arrivals = explode(';', $row['incoming_arrivals']);
			$resources = explode(';', $row['incoming_resources']);
			$merchants = explode(';', $row['incoming_merchants']);

			for ($i = 0; $i < count($arrivals); $i++) {
				$res = explode(',', $resources[$i]);

				$row['market_incoming'][] = [
					'source_village_id'   => intval($sources[$i]),
					'source_village_name' => $names[$i], // Novo
					'arrival_time'        => intval($arrivals[$i]),
					'wood'                => intval($res[0]),
					'clay'                => intval($res[1]),
					'iron'                => intval($res[2]),
					'crop'                => intval($res[3]),
					'merchant_count'      => intval($merchants[$i]),
				];
			}
		}

		// Processa os mercadores SAINDO
		if (!empty($row['outgoing_arrivals'])) {
			$targets = explode(';', $row['outgoing_targets']);
			$names = explode(';', $row['outgoing_target_names']); // Novo
			$arrivals = explode(';', $row['outgoing_arrivals']);
			$resources = explode(';', $row['outgoing_resources']);
			$merchants = explode(';', $row['outgoing_merchants']);

			for ($i = 0; $i < count($arrivals); $i++) {
				$res = explode(',', $resources[$i]);
				$row['market_outgoing'][] = [
					'target_village_id'   => intval($targets[$i]),
					'target_village_name' => $names[$i], // Novo
					'arrival_time'        => intval($arrivals[$i]),
					'wood'                => intval($res[0]),
					'clay'                => intval($res[1]),
					'iron'                => intval($res[2]),
					'crop'                => intval($res[3]),
					'merchant_count'      => intval($merchants[$i]),
				];
			}
		}

		// Processa os mercadores RETORNANDO
		if (!empty($row['returning_arrivals'])) {
			$names = explode(';', $row['returning_target_names']); // Novo
			$arrivals = explode(';', $row['returning_arrivals']);
			$merchants = explode(';', $row['returning_merchants']);

			for ($i = 0; $i < count($arrivals); $i++) {
				$row['market_returning'][] = [
					'visited_village_name' => $names[$i], // Novo
					'arrival_time'         => intval($arrivals[$i]),
					'merchant_count'       => intval($merchants[$i]),
				];
			}
		}

		// Limpa as colunas originais
		unset(
			$row['incoming_sources'], $row['incoming_source_names'], $row['incoming_arrivals'], $row['incoming_resources'], $row['incoming_merchants'],
			$row['outgoing_targets'], $row['outgoing_target_names'], $row['outgoing_arrivals'], $row['outgoing_resources'], $row['outgoing_merchants'],
			$row['returning_target_names'], $row['returning_arrivals'], $row['returning_merchants']
		);
		
		// Processa os artefatos que estão NA VILA (pode incluir artefatos de escopo de vila, size 1)
		if (!empty($row['vilart_ids'])) {
			$ids        = explode(';', $row['vilart_ids']);
			$owners     = explode(';', $row['vilart_owners']);
			$types      = explode(';', $row['vilart_types']);
			$sizes      = explode(';', $row['vilart_sizes']);
			$actives    = explode(';', $row['vilart_actives']);
			$conquereds = explode(';', $row['vilart_conquereds']);
			
			for ($i = 0; $i < count($ids); $i++) {
			    $artefact_id = intval($ids[$i]);

				$row['artefacts'][] = [
						'id'        => $artefact_id,
						'vref'      => intval($row['wref']), // Sabemos que está nesta vila
						'owner'     => intval($owners[$i]),
						'type'      => intval($types[$i]),
						'size'      => intval($sizes[$i]),
						'active'    => intval($actives[$i]),
						'conquered' => intval($conquereds[$i]),
						'scope'     => 'village' // Indica que é um artefato de escopo de vila
					];
				$processed_artefact_ids[] = $artefact_id;
			}
		}
		
		// Processa os artefatos DA CONTA (size 2 e 3) que influenciam todas as vilas
		if (!empty($row['accart_ids'])) {
			$owners     = explode(';', $row['accart_owners']);
			$ids        = explode(';', $row['accart_ids']);
			$vrefs      = explode(';', $row['accart_vrefs']);
			$types      = explode(';', $row['accart_types']);
			$sizes      = explode(';', $row['accart_sizes']);
			$actives    = explode(';', $row['accart_actives']);
			$conquereds = explode(';', $row['accart_conquereds']);

			for ($i = 0; $i < count($ids); $i++) {
				$artefact_id = intval($ids[$i]);
				$artefact_owner = intval($owners[$i]);

				$scope = 'account'; // Indica que é um artefato de escopo de conta
				if ($artefact_owner != $session->uid) $scope = 'alliance';

				// Usamos o ID do artefato como chave para evitar duplicatas facilmente
				if (!in_array($artefact_id, $processed_artefact_ids)) {
					$row['artefacts'][] = [
						'id'        => $artefact_id,
						'vref'      => intval($vrefs[$i]),
						'owner'     => $artefact_owner,
						'type'      => intval($types[$i]),
						'size'      => intval($sizes[$i]),
						'active'    => intval($actives[$i]),
						'conquered' => intval($conquereds[$i]),
						'scope'     => $scope
					];
					$processed_artefact_ids[] = $artefact_id; 
				}
			}
		}
		
		unset(
			$row['village_artifact_ids'], $row['vilart_owners'], $row['village_artifact_types'], $row['village_artifact_sizes'], $row['vilart_actives'], $row['vilart_conquereds'],
			$row['account_artifact_ids'], $row['accart_owners'], $row['account_artifact_vrefs'], $row['account_artifact_types'], $row['account_artifact_sizes'], $row['account_artifact_actives'], $row['accart_conquereds']
		);
		
		$vilas[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($vilas);
    exit;
	
}


// BUSCA VILAS CROP PARA EXPANSÃO (CropFinder)
if ((isset($_POST['cropFinder']) && $_POST['cropFinder'] == 1 || isset($_GET['cropFinder']) && $_GET['cropFinder'] == 1) && isset($session) && $session->logged_in) {
	$x = isset($_POST['x']) ? intval($_POST['x']) : (isset($_GET['x']) ? intval($_GET['x']) : 200);
	$y = isset($_POST['y']) ? intval($_POST['y']) : (isset($_GET['y']) ? intval($_GET['y']) : 200);
	$radius = isset($_POST['radius']) ? intval($_POST['radius']) : 200;
	$minBonus = isset($_POST['minBonus']) ? intval($_POST['minBonus']) : 150;
	$owner_id = $session->uid;

	// Excluir membros da própria aliança
	$owner_allyid = $session->alliance;
	$alyCondition = '';
	if ($owner_allyid > 0) {
		$alyCondition = 'AND (users.alliance IS NULL OR users.alliance != ' . $owner_allyid . ')';
	}

	// fieldtype: 0=todos, 6=crop15, 1=crop9, 7=crop7 (7,8,9)
	$fieldtypeCondition = '';
	if (isset($_POST['fieldtype'])) {
		$ft = intval($_POST['fieldtype']);
		if ($ft == 6) {
			$fieldtypeCondition = 'AND wdat.fieldtype = 6';
		} elseif ($ft == 1) {
			$fieldtypeCondition = 'AND wdat.fieldtype = 1';
		} elseif ($ft == 7) {
			$fieldtypeCondition = 'AND wdat.fieldtype IN (7, 8, 9)';
		} elseif ($ft == 0) {
			// sem filtro de fieldtype — retorna tudo
		}
	} else {
		$fieldtypeCondition = 'AND wdat.fieldtype IN (1, 6, 7, 8, 9)';
	}

	// IDs para excluir (vilas do próprio user + opcionais)
	$excludeIds = [$owner_id];
	if (!empty($_POST['excludeIds'])) {
		$extraIds = explode(',', $_POST['excludeIds']);
		foreach ($extraIds as $eid) {
			$eid = intval(trim($eid));
			if ($eid > 0) $excludeIds[] = $eid;
		}
	}
	$excludeStr = implode(',', $excludeIds);

	// Bounding box para performance
	$minX = $x - $radius;
	$maxX = $x + $radius;
	$minY = $y - $radius;
	$maxY = $y + $radius;

	$q = "SELECT wdat.id, wdat.x, wdat.y, wdat.fieldtype, wdat.occupied, wdat.oasistype,
				 vdat.owner, wdat.id as wref
		  FROM " . TB_PREFIX . "wdata AS wdat
		  LEFT JOIN " . TB_PREFIX . "vdata AS vdat ON vdat.wref = wdat.id
		  LEFT JOIN " . TB_PREFIX . "users AS users ON vdat.owner = users.id
		  WHERE wdat.id NOT IN (SELECT wref FROM " . TB_PREFIX . "vdata WHERE owner IN ($excludeStr) AND wref IS NOT NULL)
			AND (vdat.owner IS NULL OR vdat.owner NOT IN ($excludeStr))
			AND vdat.capital = 0
			AND wdat.oasistype = 0
			AND wdat.x BETWEEN $minX AND $maxX
			AND wdat.y BETWEEN $minY AND $maxY
			$alyCondition
			$fieldtypeCondition
		  ORDER BY SQRT(POW(wdat.x - $x, 2) + POW(wdat.y - $y, 2)) ASC";

	$result = mysqli_query($database->dblink, $q);
	$rows = [];
	while ($row = mysqli_fetch_assoc($result)) {
		$rows[] = $row;
	}

	// Calcular bônus de oásis para cada vila (mesma lógica do crop_finder.php)
	$worldMax = defined('WORLD_MAX') ? WORLD_MAX : 400;
	$maxCoord = 2 * $worldMax + 1;

	// Pré-carregar todos os oásis da região expandida (±3) em memória
	$oasisMinX = $minX - 3;
	$oasisMaxX = $maxX + 3;
	$oasisMinY = $minY - 3;
	$oasisMaxY = $maxY + 3;
	$q_oasis_map = "SELECT x, y, oasistype FROM " . TB_PREFIX . "wdata
					WHERE oasistype > 0
					  AND x BETWEEN $oasisMinX AND $oasisMaxX
					  AND y BETWEEN $oasisMinY AND $oasisMaxY";
	$r_oasis_map = mysqli_query($database->dblink, $q_oasis_map);
	$oasis_map = [];
	while ($o = mysqli_fetch_assoc($r_oasis_map)) {
		$oasis_map[$o['x'] . '|' . $o['y']] = (int)$o['oasistype'];
	}

	$results = [];
	foreach ($rows as $row) {
		$bonusCrop = 0;
		$totBonus50 = 0;
		$totBonus25 = 0;
		$xStart = $row['x'] - 3;
		$xEnd = $row['x'] + 3;
		$yStart = $row['y'] - 3;
		$yEnd = $row['y'] + 3;

		for ($bx = $xStart; $bx <= $xEnd; $bx++) {
			if ($totBonus50 >= 3) break;
			for ($by = $yStart; $by <= $yEnd; $by++) {
				if ($totBonus50 >= 3) break;
				// Coordenada toroidal para buscar oásis
				$wx = $bx;
				$wy = $by;
				if ($wx < -$worldMax) $wx += $maxCoord;
				if ($wx > $worldMax) $wx -= $maxCoord;
				if ($wy < -$worldMax) $wy += $maxCoord;
				if ($wy > $worldMax) $wy -= $maxCoord;

				$key = $wx . '|' . $wy;
				$oasistype = isset($oasis_map[$key]) ? $oasis_map[$key] : 0;

				if ($oasistype == 12) $totBonus50++;
				elseif (in_array($oasistype, [3, 6, 9, 10, 11])) $totBonus25++;
			}
		}

		if ($totBonus50 < 3) {
			$sub = 3 - $totBonus50;
			if ($totBonus25 > $sub) $totBonus25 = $sub;
		}
		$bonusCrop = $totBonus50 * 50 + $totBonus25 * 25;

		if ($minBonus > 0 && $bonusCrop < $minBonus) continue;

		// Distância euclidiana
		$dist = sqrt(pow($row['x'] - $x, 2) + pow($row['y'] - $y, 2));

		// Descobrir dono se ocupado
		$ownerName = '';
		if ($row['occupied'] > 0) {
			$q_owner = "SELECT username FROM " . TB_PREFIX . "users WHERE id = " . intval($row['owner']);
			$r_owner = mysqli_query($database->dblink, $q_owner);
			if ($ro = mysqli_fetch_assoc($r_owner)) {
				$ownerName = $ro['username'];
			}
		}

		$results[] = [
			'id' => intval($row['id']),
			'x' => intval($row['x']),
			'y' => intval($row['y']),
			'fieldtype' => intval($row['fieldtype']),
			'occupied' => intval($row['occupied']),
			'owner' => intval($row['owner']),
			'owner_name' => $ownerName,
			'oasis_bonus' => $bonusCrop,
			'distance' => round($dist, 1)
		];
	}

	// Ordenar: crop15 primeiro, depois crop9, depois crop7; depois por distância
	usort($results, function($a, $b) {
		$ftA = $a['fieldtype'];
		$ftB = $b['fieldtype'];
		$prioA = ($ftA == 6) ? 0 : (($ftA == 1) ? 1 : 2);
		$prioB = ($ftB == 6) ? 0 : (($ftB == 1) ? 1 : 2);
		if ($prioA != $prioB) return $prioA - $prioB;
		return $a['distance'] - $b['distance'];
	});

	header('Content-Type: application/json');
	echo json_encode(['villages' => $results]);
	exit;
}

// RENOMEAR VILA
if (isset($_POST['renameVillage']) && $_POST['renameVillage'] == 1 && isset($session) && $session->logged_in) {
	$vref = intval($_POST['vref']);
	$newName = trim($_POST['name']);

	if ($vref <= 0 || empty($newName)) {
		header('Content-Type: application/json');
		echo json_encode(['success' => false, 'error' => 'Parâmetros inválidos']);
		exit;
	}

	// Verificar se a vila pertence ao usuário
	$q_check = "SELECT wref FROM " . TB_PREFIX . "vdata WHERE wref = $vref AND owner = $session->uid";
	$r_check = mysqli_query($database->dblink, $q_check);
	if (mysqli_num_rows($r_check) == 0) {
		header('Content-Type: application/json');
		echo json_encode(['success' => false, 'error' => 'Vila não encontrada ou não pertence ao usuário']);
		exit;
	}

	$newNameSafe = mysqli_real_escape_string($database->dblink, $newName);
	$q_update = "UPDATE " . TB_PREFIX . "vdata SET name = '$newNameSafe' WHERE wref = $vref";
	$success = mysqli_query($database->dblink, $q_update);

	header('Content-Type: application/json');
	echo json_encode(['success' => $success, 'name' => $newNameSafe]);
	exit;
}

// BUSCA VILAS PROXIMAS (lightweight, para suporte inicial e cross-group)
if (isset($_POST['findVillage']) && $_POST['findVillage'] == 1 && isset($session) && $session->logged_in) {
	$x = intval($_POST['x']);
	$y = intval($_POST['y']);
	$radius = isset($_POST['radius']) ? intval($_POST['radius']) : 5;
	$preferUnoccupied = isset($_POST['preferUnoccupied']) ? intval($_POST['preferUnoccupied']) : 0;
	$excludeFieldtypes = isset($_POST['excludeFieldtypes']) ? $_POST['excludeFieldtypes'] : '';

	$fieldtypeExclude = '';
	if (!empty($excludeFieldtypes)) {
		$types = array_map('intval', explode(',', $excludeFieldtypes));
		$typesStr = implode(',', $types);
		if (!empty($typesStr)) {
			$fieldtypeExclude = "AND wdat.fieldtype NOT IN ($typesStr)";
		}
	}

	$minX = $x - $radius;
	$maxX = $x + $radius;
	$minY = $y - $radius;
	$maxY = $y + $radius;

	$q = "SELECT wdat.id, wdat.x, wdat.y, wdat.fieldtype, wdat.occupied, vdat.owner
		  FROM " . TB_PREFIX . "wdata AS wdat
		  LEFT JOIN " . TB_PREFIX . "vdata AS vdat ON vdat.wref = wdat.id
		  WHERE wdat.oasistype = 0
			AND wdat.x BETWEEN $minX AND $maxX
			AND wdat.y BETWEEN $minY AND $maxY
			$fieldtypeExclude
		  ORDER BY SQRT(POW(wdat.x - $x, 2) + POW(wdat.y - $y, 2)) ASC
		  LIMIT 20";

	$result = mysqli_query($database->dblink, $q);
	$villages = [];
	while ($row = mysqli_fetch_assoc($result)) {
		$dist = sqrt(pow($row['x'] - $x, 2) + pow($row['y'] - $y, 2));
		$villages[] = [
			'id' => intval($row['id']),
			'x' => intval($row['x']),
			'y' => intval($row['y']),
			'fieldtype' => intval($row['fieldtype']),
			'occupied' => intval($row['occupied']),
			'owner' => intval($row['owner'] ?? 0),
			'distance' => round($dist, 1)
		];
	}

	if ($preferUnoccupied) {
		usort($villages, function($a, $b) {
			if ($a['occupied'] != $b['occupied']) return $a['occupied'] - $b['occupied'];
			return $a['distance'] - $b['distance'];
		});
	}

	header('Content-Type: application/json');
	echo json_encode(['villages' => $villages]);
	exit;
}

// CHECK VILLAGE OCCUPATION (lightweight, para fundação)
if (isset($_POST['checkVillage']) && $_POST['checkVillage'] == 1 && isset($session) && $session->logged_in) {
    $vref = intval($_POST['vref']);
    $q = "SELECT wdat.id, wdat.occupied, vdat.owner 
          FROM " . TB_PREFIX . "wdata AS wdat 
          LEFT JOIN " . TB_PREFIX . "vdata AS vdat ON vdat.wref = wdat.id 
          WHERE wdat.id = $vref";
    $result = mysqli_query($database->dblink, $q);
    if ($row = mysqli_fetch_assoc($result)) {
        header('Content-Type: application/json');
        echo json_encode(['id' => intval($row['id']), 'occupied' => intval($row['occupied']), 'owner' => intval($row['owner'] ?? 0)]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'not_found']);
    }
    exit;
}

if (isset($_POST['checkVillageBuildings']) && $_POST['checkVillageBuildings'] == 1 && isset($session) && $session->logged_in) {
    $vref = intval($_POST['vref']);

    $q = "SELECT
    v.owner,
    v.name,
    v.loyalty,
		CASE
			WHEN 25 IN (f.f19t, f.f20t, f.f21t, f.f22t, f.f23t, f.f24t, f.f25t, f.f26t, f.f27t, f.f28t, f.f29t, f.f30t, f.f31t, f.f32t, f.f33t, f.f34t, f.f35t, f.f36t, f.f37t, f.f38t, f.f39t) THEN 1
			ELSE 0
		END AS temResid,
		CASE
			WHEN 26 IN (f.f19t, f.f20t, f.f21t, f.f22t, f.f23t, f.f24t, f.f25t, f.f26t, f.f27t, f.f28t, f.f29t, f.f30t, f.f31t, f.f32t, f.f33t, f.f34t, f.f35t, f.f36t, f.f37t, f.f38t, f.f39t) THEN 1
			ELSE 0
		END AS temPalac
	FROM
		" . TB_PREFIX . "vdata v
	INNER JOIN
		" . TB_PREFIX . "fdata f ON v.wref = f.vref
	WHERE
		v.wref = $vref";
    $result = mysqli_query($database->dblink, $q);

    if ($row = mysqli_fetch_assoc($result)) {
        header('Content-Type: application/json');
        echo json_encode([
			'owner' => intval($row['owner']),
			'name' => $row['name'],
			'loyalty' => $row['loyalty'],
			'temResid' => intval($row['temResid'] ?? 0),
			'temPalac' => intval($row['temPalac'] ?? 0)			
			]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'not_found']);
    }
    exit;
}

if (isset($_GET['test']) && $_GET['test'] == 1) {

	header('Content-Type: application/json');
	echo json_encode(time());
	exit;
}

// Nenhum parâmetro válido foi enviado
header('Content-Type: application/json');
echo json_encode(["error" => "Parâmetros inválidos"]);
exit;

?>