<?php
	include_once("simple_html_dom.php");
	$base_url = "http://school.nihaowang.com/__c__-0-0-0-0-0-0-0-0-0-0-2-2-5-0-0-0-0-0-0-__p__-01.html";
	$root = "http://school.nihaowang.com/";
	$country_arr = array("175"=>"美国","69"=>"英国","150"=>"澳大利亚","174"=>"加拿大","74"=>"法国","5"=>"日本","65"=>"德国","151"=>"新西兰","4"=>"韩国","58"=>"俄罗斯","50"=>"瑞典","71"=>"荷兰","85"=>"意大利","89"=>"西班牙","70"=>"爱尔兰","14"=>"新加坡","12"=>"马来西亚","11"=>"泰国","51"=>"挪威","49"=>"芬兰","53"=>"丹麦","67"=>"瑞士","59"=>"乌克兰","81"=>"希腊","77"=>"保加利亚","61"=>"波兰","6"=>"菲律宾","72"=>"比利时","142"=>"南非","66"=>"奥地利","64"=>"匈牙利","48"=>"塞浦路斯","92"=>"埃及","186"=>"古巴","90"=>"葡萄牙","76"=>"罗马尼亚","112"=>"喀麦隆","57"=>"白俄罗斯","96"=>"阿尔及利亚","147"=>"毛里求斯","22"=>"斯里兰卡","25"=>"吉尔吉斯斯坦","55"=>"拉脱维亚","68"=>"列支敦士登","35"=>"以色列","227"=>"香港","228"=>"澳门","229"=>"台湾");


	$mysqli = new mysqli('localhost', 'root', '', 'fu');
	if ($mysqli->connect_error) {
	    die('Connect Error (' . $mysqli->connect_errno . ') '  . $mysqli->connect_error);
	}
	$mysqli->query("SET NAMES 'utf8'");
	$mysqli->autocommit(TRUE);
 	getWebsite();
 	// getSchDetail();
 	// getMeta();
	// getAllLists();
	// getCountryDetail();
	// getSchDetail();
	// getRanks();
	// getTanData();
	$mysqli->close();
	


	function getWebsite()
	{
		global $mysqli;
		$ssql = <<<EOD
		select * from schlist;
EOD;
		if ($result = $mysqli->query($ssql)) {
 			while($obj = $result->fetch_object()){
 				$lastid = $obj->id;
 				$uid = $obj->uid;
 				if ($lastid < 12175) {
 					continue;
 				}
 				$detail_url = $obj->detail_url;
 				$chs_name = mysql_str($obj->chs_name);

 				echo "id:\t$lastid\t$uid\t$detail_url\tStart!\n";
 				$html  = file_get_html($detail_url);
 				while (!is_object($html)) {
 					$html  = file_get_html($detail_url);
 					echo ".";
 				}
 				$web = $html->find('.school_list div[style] a',0);
 				$iWebsite = "";
 				if (is_object($web)) {
 					$iWebsite = mysql_str($web->plaintext);	
 				}
 				$sql = <<<EOD
 				update schdetail set website="{$iWebsite}" where uid = "{$uid}";
EOD;

 				if ($mysqli->query($sql)) {
					echo "update {$chs_name}\t {$iWebsite} done\n";
					free_mysqli();
				}else{
					print_r($mysqli->error);
					exit(1);
				}

				$html->clear();
			}
 		}else{
			print_r($mysqli->error);
			exit(1);
		}
		$result->close();
		free_mysqli();


	}
	function getMeta()
	{
		global $mysqli;
		$ssql = <<<EOD
		select * from schlist;
EOD;
		if ($result = $mysqli->query($ssql)) {
 			while($obj = $result->fetch_object()){
 				$lastid = $obj->id;
 				if ($lastid < 191) {
 					continue;
 				}
 				$detail_url = $obj->detail_url;
 				$eng_name = mysql_str($obj->eng_name);

 				echo "id:\t$lastid\t\t$detail_url\tStart!\n";
 				$html  = file_get_html($detail_url);
 				while (!is_object($html)) {
 					$html  = file_get_html($detail_url);
 					echo ".";
 				}
 				$title = mysql_str($html->find('title',0)->plaintext);
 				$tmp = explode("_", $title);

 				$new_eng_name = mysql_str($tmp[1]);

 				$sql = <<<EOD
update schlist set foreign_name = "{$eng_name}",eng_name="{$new_eng_name}" where id={$lastid};
EOD;
				if ($mysqli->query($sql)) {
					echo "update {$new_eng_name} done\n";
					free_mysqli();
				}else{
					print_r($mysqli->error);
					exit(1);
				}

				$html->clear();
			}
 		}else{
			print_r($mysqli->error);
			exit(1);
		}
		$result->close();
		free_mysqli();

	}

	function getTanData()
	{
//		createTanTable();		
		global $mysqli;
		$cccurl = "http://tan.nihaowang.com/list-new-_page_.html";
		for ($ipage=1; $ipage <= 1421; $ipage++) {
			if($ipage < 577) continue;
			$url = str_replace("_page_", $ipage, $cccurl);
			echo "Page:{$ipage}\t$url\nStart!\n";
			$html = file_get_html($url);
			while(!is_object($html))
			{
				$html = file_get_html($url);
			}
			$table = $html->find('#dlArticle',0);
			foreach ($table->find('li') as $idx => $li) {
				$lia = $li->find('.cl_tob_img a',0);
				$durl = $lia->href;
				$dtitle = mysql_str($lia->getAttribute('title'));							//1
				$tanid = basename(basename($durl),".html");						//2
				$liimg = $li->find('.cl_tob_img img',0);
				$imgfield = "";													//3
				if (is_object($liimg)) {
					$imgsrc = $liimg->getAttribute('src');
					$imgret = getImage($imgsrc,"./tan/{$tanid}",basename($imgsrc));
					$imgfield = $imgret['error'];
					if ($imgfield == 0) {
						$imgfield = mysql_str($imgret['save_path']);
					}
				}
				$timetile = mysql_str($li->find(".cl_tob_r_4",0)->plaintext);
				$dhtml = file_get_html($durl);
				$xxxx = 0;
				while(!is_object($dhtml))
				{	$xxxx ++;
					$dhtml = file_get_html($durl);
					if($xxxx > 3)
					{
						$xxxx = 55;
						break;
					}
				}
				if($xxxx == 55)
				{       echo "continue\n";
					continue;
				}
				$infolis = $dhtml->find('#lblGuests li');
				$iGname = "";
				$iCountry = "";
				$iCountryEng = "";
				$iSchoolName = "";
				$iSchid = "";
				$iMajor = "";
				$iDegree = "";
				$iSchTime = "";
				$iDepartment = "";
				$iRealname = "";
				$iEmail = "";
				$iQQ = "";
				$iOther = "";
				foreach ($infolis as $_idx => $infoli) {
					$t = trim($infoli->plaintext);
					if ($t == "") {
						continue;
					}
					$t0 = explode("：",$t);
					$t1 = trim($t0[0]);
					$t2 = mysql_str($t0[1]);

					if ($t1 == "本期嘉宾") {
						$iGname = $t2;
					}else if ($t1 == "就读学校") {
						$jiudu = $dhtml->find('#lblGuests li',$_idx);
						$c = $jiudu->find('a',0);
						$s = $jiudu->find('a',1);
						$iCountry = $c->plaintext;
						$iCountryEng = basename($c->href);
						$iSchoolName = $s->plaintext;
						$iSchid = basename((basename($s->href)),".html");
					}else if ($t1 == "专业") {
						$iMajor = $t2;
					}else if ($t1 == "学历") {
						$iDegree = $t2;
					}else if ($t1 == "在校时间") {
 						$iSchTime = $t2;
					}else if ($t1 == "院系") {
						$iDepartment = $t2;
					}else if ($t1 == "真实姓名") {
						$iRealname = $t2;
					}else if ($t1 == "邮箱") {
						$iEmail = $t2;
					}else if ($t1 == "QQ") {
						$iQQ = $t2;
					}else{
						$iOther = mysql_str("{$t1}:{$t2}");
					}
				}

				$content = $dhtml->find('.sign_cons',0);
				$dhtml->set_callback("removeDom");
				$cont = $content->outertext;
				$dhtml->remove_callback();

				$cont = str_replace("><br />", "", $cont);
				$cont = str_replace("<br />", "\n", $cont);
				$cont = mysql_str($cont);
				
				$sql = <<<EOD
insert into nh_tan (id,tanid,title,image,timetile,guest_name,country,country_eng,school,schid,major,degree,schtime,department,realname,email,qq,other,content,created)
values (null,{$tanid},"{$dtitle}","{$imgfield}","{$timetile}","{$iGname}","{$iCountry}","{$iCountryEng}","{$iSchoolName}",{$iSchid},"{$iDepartment}","{$iMajor}","{$iDegree}","{$iSchTime}","{$iRealname}","{$iEmail}","{$iQQ}","{$iOther}","{$cont}",now());
EOD;
				if ($mysqli->query($sql)) {
					echo "insert {$dtitle}\t{$iSchoolName} done\n";
					free_mysqli();
				}else{
					print_r($mysqli->error);
					exit(1);
				}

				$dhtml->clear();
			}

			$html->clear();
		}

	}
	function removeDom($element)
	{
		if ($element->tag == "div" && $element->getAttribute('class') !="sign_cons") {
			$element->outertext = "";
		}
		if ($element->tag == "span") {
			$element->outertext = "";	
		}
		if ($element->tag == "p" && $element->getAttribute('class') == "key") {
			$element->outertext = "";
		}
		if (trim($element->plaintext) == "&nbsp;") {
			$element->outertext = "";	
		}
	}

	function getRanks()
	{
		createRankListTable();
		global $mysqli;
		$webroot = "http://school.nihaowang.com";
		$subwebroot = "http://school.nihaowang.com/rank/";
		$rankurl = array("/rank/3-0.html"=>"各国院校排名","/rank/1-0.html"=>"全球院校排名","/rank/4-0.html"=>"各国专业排名","/rank/2-0.html"=>"全球专业排名","/rank/6-0.html"=>"各国商学院排名","/rank/5-0.html"=>"全球商学院排名");

		foreach ($rankurl as $rurl => $rtype) {
			$rurl = $webroot . $rurl;
			$html = file_get_html($rurl);
			echo "URLv1\t" . $rurl . "\tStart!\n";
			$rank_names = $html->find('.switching .hover01');
			foreach ($rank_names as $_idxv1 => $namev1) {
				$_name = $namev1->find('a',0)->plaintext;
				$_href = $namev1->find('a',0)->href;
				$_brief = str_replace("查看更多>>", "", $namev1->find('p',0)->plaintext);

				$rurlv2 = $webroot . $_href;
				echo "URLv2\t" . $rurlv2 . "\tStart!\n";
				$htmlv2 = file_get_html($rurlv2);
				$sql = <<<EOD
insert into nh_ranklistv1 (id,type,name,brief,created)
values (null,"{$rtype}","{$_name}","{$_brief}",now());
EOD;
				$v1id = 0;
				if ($mysqli->query($sql)) {
					$v1id = $mysqli->insert_id;
					echo "=insert ranklistv1\t{$_name} done\n";
					free_mysqli();
				}else{
					print_r($mysqli->error);
					exit(1);
				}
				$paiming_list = $htmlv2->find('.paiming_list',0);
				$lista = $paiming_list->find('a');
				foreach ($lista as $_idxv2 => $aaav2) {
					$__href = $aaav2->href;
					
					$__name = $aaav2->getAttribute('title');
					$__year = rtrim(ltrim(str_replace($__name, "", $aaav2->plaintext),'('),')');

					$rurlv3 = $webroot . $__href;
					$rankid = basename(basename($__href),".html");
					$sql = <<<EOD
insert into nh_ranklistv2 (id,v1id,v2id,name,year,created)
values (null,{$v1id},{$rankid},"{$__name}","{$__year}",now());
EOD;
					if ($mysqli->query($sql)) {
						echo "==insert ranklistv2\t{$__name}({$__year}) done\n";
						free_mysqli();
					}else{
						print_r($mysqli->error);
						exit(1);
					}
					echo "URLv3\t" . $rurlv3 . "\tStart!\n";
					$htmlv3 = file_get_html($rurlv3);
					$termipage = $htmlv3->find('.termipag a',0);
					if (is_object($termipage)) {
						$lastpage = $termipage->href;
						$pagenum = basename(basename($lastpage),".html");
						$ipagenum = (int)$pagenum;

						for ($i=1; $i <= $ipagenum; $i++) { 
							$rurlv4 = $subwebroot . $rankid . "/{$i}.html";
							echo "URLv4\t" . $rurlv4 . "\tStart!\n";
							$htmlv4 = file_get_html($rurlv4);
							$ranking_li = $htmlv4->find('.ranking li');
							foreach ($ranking_li as $___idx => $___li) {
								if ($___idx == 0) {
									continue;
								}
								$___spans = $___li->find('span');
								$rid    = $___spans[0]->find('a',0)->plaintext;
								$rsid = 0;
								if (is_object($slink)) {
									$rsid 	= basename(basename($slink->href),".html");	
								}
								$rsname = mysql_str($___spans[1]->find('text',0)->plaintext);
								$rsename = mysql_str($___spans[1]->find('h1',0)->plaintext);
								$rjiaoren = 0;
								$rjiaoren_img = $___spans[1]->find('img',0);
								if (is_object($rjiaoren_img)) {
									$rjiaoren = 1;
								}
								$rloc   = mysql_str($___spans[2]->plaintext);
								$sql = <<<EOD
insert into nh_ranklistv3 (id,v2id,rid,sid,sch_name,eng_name,jiaoren,location,created)
values (null,{$rankid},{$rid},{$rsid},"{$rsname}","{$rsename}",{$rjiaoren},"{$rloc}",now());
EOD;
								if ($mysqli->query($sql)) {
									echo "===insert ranklistv3\t{$rid}:{$rsname}\tpage:{$i} done\n";
									free_mysqli();
								}else{
									print_r($mysqli->error);
									exit(1);
								}

							}

							$htmlv4->clear();
						}
					}else{
						$ranking_li = $htmlv3->find('.ranking li');
						foreach ($ranking_li as $___idx => $___li) {
							if ($___idx == 0) {
								continue;
							}
							$___spans = $___li->find('span');
							$rid    = mysql_str($___spans[0]->find('a',0)->plaintext);
							$slink = mysql_str($___spans[1]->find('a',0));
							$rsid = 0;
							if (is_object($slink)) {
								$rsid 	= basename(basename($slink->href),".html");	
							}
							$rsname = mysql_str($___spans[1]->find('text',0)->plaintext);
							$rsename = mysql_str($___spans[1]->find('h1',0)->plaintext);
							$rjiaoren = 0;
							$rjiaoren_img = $___spans[1]->find('img',0);
							if (is_object($rjiaoren_img)) {
								$rjiaoren = 1;
							}
							$rloc   = mysql_str($___spans[2]->plaintext);
							$sql = <<<EOD
insert into nh_ranklistv3 (id,v2id,rid,sid,sch_name,eng_name,jiaoren,location,created)
values (null,{$rankid},{$rid},{$rsid},"{$rsname}","{$rsename}",{$rjiaoren},"{$rloc}",now());
EOD;
							if ($mysqli->query($sql)) {
								echo "===insert ranklistv3\t{$rid}:{$rsname} done\n";
								free_mysqli();
							}else{
								print_r($mysqli->error);
								exit(1);
							}
						}
					}
					

					$htmlv3->clear();
				}

				$htmlv2->clear();

			}
			$html->clear();

		}

	}

	function getSchDetail()
	{
		// createSchoolDetail();

		global $mysqli,$root;

		$major_code = array(1=>"法学",2=>"工学",3=>"管理学",4=>"教育学",5=>"经济学",6=>"理学",7=>"历史学",8=>"农学",9=>"文学",10=>"医学",11=>"哲学",12=>"军事",13=>"职教及其他类别");
		$degree_id  = array(4,5,7,6,3,1,10,2,8,9);
		$degree_name= array("本科","硕士","博士","MBA","专科","语言中心","研究生证书与文凭","预科","副博士","专家");
		$degree_code = array("Undergraduate","Master","Dr","MBA","Specialist","Language","NetWork","Foundation","ViceDr","Professional");

		$operat = "http://school.nihaowang.com/Ashx/UniversityOperation.ashx";
		$cpicurl = "http://school.nihaowang.com/Ashx/CreatePic.ashx";
// 		$ssql = <<<EOD
// 		select * from nh_schlist;
// EOD;
		$ssql = <<<EOD
		select * from schlist where id > 12789;
EOD;
		if ($result = $mysqli->query($ssql)) {
 			while($obj = $result->fetch_object()){
 				$lastid = $obj->id;
 				$cid = $obj->cid;
 				$schnid = $obj->schnid;
 				$detail_url = $obj->detail_url;
 				$chs_name = $obj->chs_name;
 				$eng_name = $obj->eng_name;
 				$jiaoren = $obj->jiaoren;
 				$number	 = $obj->number;
 				$thumb   = $obj->thumb;

 				echo "id:$lastid\t$detail_url\nStart!\n";
 				$html  = file_get_html($detail_url); 				
 				$main_two = $html->find('.main_two',0);
 				$left 	  = $main_two->find('.left_14',0);
 				$right 	  = $main_two->find('.right_14',0);
 				
 				$renqi    = mysql_str($left->find('.ad_title .left_15_sort span',0)->plaintext);

 				$iState   = "";
 				$iCity 	  = "";
 				$iProp	  = "";
 				$iEnroll  = "";
 				$iBuild	  = "";
 				$iPop     = "";
 				$iWebsite = "";
 				$iInter   = "";
 				$iZuyi	  = "";
 				$iCommonApp = "否";
 				$infos    = $main_two->find('#ul_InfoBase li');
 				foreach ($infos as $idx => $li) {
 					$span = trim($li->find('text',0)->plaintext);
 					if ($span == "") {
 						continue;
 					}
 					$vli  = mysql_str(trim($li->find('text',1)->plaintext));
 					if ($span == "省州：") {
 						$iState = $vli;
 					}
 					if ($span == "城市：") {
 						$iCity = $vli;
 					}
 					if ($span == "性质：") {
 						$iProp = $vli;
 					}
 				    if ($span == "录取率：") {
 						$iEnroll = $vli;
 					}	
 					if ($span == "建校年代：") {
 						$iBuild = $vli;
 					}
 					if ($span == "人数：") {
 						$iPop = $vli;
 					}
 					if ($span == "国际学生比例：") {
 						$iInter = $vli;
 					}
 					if ($span == "官网：") {
 						$iWebsite = $vli;
 					}
 					if ($span == "族裔比例：") {
 						$iZuyi = $vli;
 					} 
 					if ($span == "接受CommonApp申请：") {
 						$iCommonApp =  $vli;				
 					}			
 				}
 				$iBrief   = mysql_str($main_two->find('#introInfo2',0)->find('text',0));
 				echo ".";
 				$imgarr = array();
 				$images = $right->find('#imgList ul img');
 				foreach ($images as $idx => $img) {
 					$alt = $img->getAttribute('alt');
 					$imgsrc = $img->getAttribute('src');
 					$imgsrc = str_replace("Thumbnail/", "",$imgsrc);
 					$img_ret = getImage($imgsrc,"./school/{$cid}/{$schnid}/",basename($imgsrc));
 					$imgpath = $img_ret['error'];
 					if ($img_ret['error'] == 0) {
 						$imgpath = $img_ret['save_path'];
 					}
					$imgarr[$alt] = $imgpath;
					echo ".";
 				}
 				$imgfield = mysql_str(serialize($imgarr));

 				echo ".";
 				$major_summary = array();
 				$oAddress = "";
 				$oApplyOnline = "";
 				$oConditionAge = "";
 				$oConditionCost = array();
 				$oConditionEdu = "";
 				$oConditionTest = array();
 				$oConditionWork = "";
 				$oCurrency = "";
 				$oFax = "";
 				$oMail = "";
 				$oMajorSum = "";
 				$oOpeningTime = array();
 				$oOtherApplications = "";
 				$oOtherCondition = "";
 				$oOtherBooks = "";
 				$oOtherReg = "";
 				$oRate = "";
 				$oScholarshipUrl = "";
 				$oTel    	     = "";
 				$oTuition = '';
 				$oAlimony = '';
 				$oXueZhi  = '';
				$dd  = "key=~sid~_~dcode~_~cid~_5";
				$picdd = "id=~sid~&type=li_~dcode~";
 				foreach ($degree_code as $idx => $codex) {
					$oAddress = "";
	 				$oApplyOnline = "";
	 				$oConditionAge = "";
	 				$oConditionCost = array();
	 				$oConditionEdu = "";
	 				$oConditionTest = array();
	 				$oConditionWork = "";
	 				$oCurrency = "";
	 				$oFax = "";
	 				$oMail = "";
	 				$oMajorSum = "";
	 				$oOpeningTime = array();
	 				$oOtherApplications = "";
	 				$oOtherCondition = "";
	 				$oOtherBooks = "";
	 				$oOtherReg = "";
	 				$oRate = "";
	 				$oScholarshipUrl = "";
	 				$oTel    	     = "";
	 				$oTuition = '';
	 				$oAlimony = '';
	 				$oXueZhi  = '';

 					$pd = str_replace("~sid~", $schnid, $dd);
 					$pd = str_replace("~dcode~", $codex, $pd);
 					$pd = str_replace("~cid~", $cid, $pd);

 					$ret = postData($pd,$operat);
 					$origin_field = mysql_str($ret);
					$json = json_decode($ret);
	 				$oAddress 			= mysql_str_json($json->Address);
	 				if (isset($json->ApplyOnline)) {
	 					$oApplyOnline 		= mysql_str_json($json->ApplyOnline);
	 				}
	 				$oConditionAge 		= mysql_str_json($json->Conditions_Age);
	 				$oConditionCost 	= mysql_str_json($json->Conditions_Cost);
	 				$oConditionEdu 		= mysql_str_json($json->Conditions_Edu);
	 				$oConditionTest 	= mysql_str_json($json->Conditions_Test);
	 				if (isset($json->Conditions_Work)) {
	 					$oConditionWork 	= mysql_str_json($json->Conditions_Work);
	 				}
	 				$oCurrency 			= mysql_str_json($json->Currency);
	 				$oFax 				= mysql_str_json($json->Fax);
	 				$oMail 				= mysql_str_json($json->Mail);
	 				$oMajorSum 			= mysql_str_json($json->MajorSum);
	 				$oOpeningTime 		= mysql_str_json($json->OpeningTime);
	 				$oOtherApplications = mysql_str_json($json->Other_Application);
	 				$oOtherCondition 	= mysql_str_json($json->Other_Conditions);
	 				$oOtherBooks 		= mysql_str_json($json->Other_books);
	 				$oOtherReg 			= mysql_str_json($json->Other_reg);
	 				$oRate 				= mysql_str_json($json->Rate);
	 				$oScholarshipUrl 	= mysql_str_json($json->ScholarshipUrl);
	 				$oTel    	     	= mysql_str_json($json->Tel);
	 				$oTuition 			= mysql_str_json($json->Tuition);
	 				$oAlimony	 		= mysql_str_json($json->alimony);
	 				if (isset($json->xueZhi)) {
	 					$oXueZhi  			= mysql_str_json($json->xueZhi);
	 				}

	 				$pdd = str_replace("~sid~", $schnid, $picdd);
	 				$pdd = str_replace("~dcode~", $codex, $pdd);
	 				$major_ret = postData($pdd,$cpicurl,true);
	 				$major_img_arr = array();
	 				if (trim($major_ret) != "") {
						$mhtml = str_get_html($major_ret);
						$_spans = $mhtml->find('span');
						$_imgs  = $mhtml->find('img');
						foreach ($_imgs as $_idxx => $_img) {
							$_prof 	 = trim($_spans[$_idxx]->plaintext);
							$_filename = array_search($_prof,  $major_code);
							if ($_filename === FALSE) {
								$_filename = $_prof;
							}
							$_imgsrc = $_img->getAttribute('src');
							$_imgsrc = $root . $_imgsrc;
							$_profimg_ret = getImage($_imgsrc,"./major/{$cid}/{$schnid}/{$codex}/", $_filename . ".gif");
							$_profimg_path = $_profimg_ret['error'];
							if ($_profimg_path == 0) {
								$_profimg_path = $_profimg_ret['save_path'];
							}
							$major_img_arr[$_prof] = $_profimg_path; 
							echo ".";
						}
						$mhtml->clear();
	 				}
	 				$major_field = mysql_str(serialize($major_img_arr));

	 				$sql = <<<EOD
insert into nh_major (id,cid,sid,chs_name,eng_name,degree_name,address,applyonline,conditionage,conditioncost,conditionedu,conditiontest,conditionwork,currency,fax,mail,majorsum,openingtime,otherapplications,othercondition,otherbooks,otherreg,rate,scholarshipurl,tel,tuition,alimony,xuezhi,major,origin,created)
values (null,{$cid},{$schnid},"{$chs_name}","{$eng_name}","{$codex}","{$oAddress}","{$oApplyOnline}","{$oConditionAge}","{$oConditionCost}","{$oConditionEdu}","{$oConditionTest}","{$oConditionWork}","{$oCurrency}","{$oFax}","{$oMail}","{$oMajorSum}","{$oOpeningTime}","{$oOtherApplications}","{$oOtherCondition}","{$oOtherBooks}","{$oOtherReg}","{$oRate}","{$oScholarshipUrl}","{$oTel}","{$oTuition}","{$oAlimony}","{$oXueZhi}","{$major_field}","{$origin_field}",now());
EOD;
					// echo $sql . "\n";
					$mid = 0;
					if ($mysqli->query($sql)) {
						$mid = $mysqli->insert_id;
						if ($oMajorSum == 0) {
							echo "insert {$eng_name}\t{$codex} Count:{$oMajorSum} done\n";							
						}else{
							echo "\ninsert {$eng_name}\t{$codex} Count:{$oMajorSum} done\n";
						}
						free_mysqli();
					}else{
						print_r($mysqli->error);
						exit(1);
					}

	 				unset($json);

	 				if ($oAddress != "") {
	 					$major_summary[$codex] = $mid;
	 				}
 				}

 				$map = $html->find('#zk_map a',0)->href;
 				$latlng = str_replace("http://ditu.google.cn/maps?q=", "", $map);

 				$major_summary_field = mysql_str(serialize($major_summary));
 				$sql = <<<EOD
insert into schdetail (id,cid,schnid,chs_name,eng_name,jiaoren,number,thumb,latlng,renqi,state,city,prop,enrollrate,build,pop,website,international,zuyi,commonapp,brief,images,major,created)
values (null,{$cid},{$schnid},"{$chs_name}","{$eng_name}",$jiaoren,"{$number}","{$thumb}","{$latlng}","{$renqi}","{$iState}","{$iCity}","{$iProp}","{$iEnroll}","{$iBuild}","{$iPop}","{$iWebsite}","{$iInter}","{$iZuyi}","{$iCommonApp}","{$iBrief}","{$imgfield}","{$major_summary_field}",now());
EOD;
				// echo $sql . "\n";
				$mc = count($major_summary);
				if ($mysqli->query($sql)) {
					echo "insert {$eng_name}\tDegree Count:{$mc} done\n\n";
					free_mysqli();
				}else{
					print_r($mysqli->error);
					exit(1);
				}
 				$html->clear();

 			}
 		}else{
			print_r($mysqli->error);
			exit(1);
		}
		$result->close();
		free_mysqli();

	}


	function getCountryDetail()
	{
		createCountryDetailTable();
		global $mysqli;
		$ssql = <<<EOD
		select * from nh_country;
EOD;
		if ($result = $mysqli->query($ssql)) {
 			while($obj = $result->fetch_object()){
				$cid = $obj->cid;
				$cname = $obj->cname;
				$sch_count = $obj->sch_count;
				$page_count = $obj->page_count;
				$curl = $obj->country_url;

				$html = file_get_html($curl);

				$ctitle = mysql_str($html->find('.Country_01_t',0)->find('text',0)->plaintext);

				$flags = $html->find('.Country_01_c .flag_top',0);
				$detail = $html->find('.Country_01_c .Country_j',0);

				$tt = $flags->find('img');
				$flag_img = $tt[0]->getAttribute('src');
				$symbol_img = "";
				if (isset($tt[1])) {
					$symbol_img = $tt[1]->getAttribute('src');	
				}
				$flag_ret   = getImage($flag_img,"./country/flag",basename($flag_img));
				$symbol_ret = getImage($symbol_img,"./country/emblem",basename($symbol_img));

				$flag_field = $flag_ret['error'];
				if ($flag_field == 0) {
					$flag_field = $flag_ret['save_path'];
				}
				$symbol_field = $symbol_ret['error'];
				if ($symbol_field == 0) {
					$symbol_field = $symbol_ret['save_path'];
				}
				
				$dli = $detail->find('li');
				$dfullname = getCommonLast($dli[0]->plaintext);
				$dengname = getCommonLast($dli[1]->plaintext);
				$dregion  = getCommonLast($dli[2]->find('.f',0)->plaintext);
				$dcapital = getCommonLast($dli[2]->find('.f',1)->plaintext);
				$dpop     = getCommonLast($dli[3]->find('.f',0)->plaintext);
				$dlang 	  = getCommonLast($dli[3]->find('.f',1)->plaintext);


				$jianjiediv = $html->find('.Country_01 .jianjie',0);
				$jianjie    = $jianjiediv->find('p[style]');
				$brief = "";
				foreach ($jianjie as $idx => $bbb) {
					$brief .= "\n" . mysql_str($bbb);
				}
				$brief = trim($brief);

				$images  = $html->find('.Country_02 .img_jd img');
				$span 	=  $html->find('.Country_02 .img_jd span');
				$scenery = array();
				foreach ($images as $idx => $img) {
					$imgsrc = $img->getAttribute('src');
					$imgtitle = trim($span[$idx]->plaintext);

					$tt_ret = getImage($imgsrc,"./country/scenery/{$cid}",basename($imgsrc));

					if ($tt_ret['error'] == 0) {
						$scenery[$imgtitle] = $tt_ret['save_path'];
					}
				}
				$scenery_field = mysql_str(serialize($scenery));

				$scenery_jianjie = $html->find('.Country_02 .jianjie p[style]');
				$scenery_brief = "";
				foreach ($scenery_jianjie as $idx => $ppp) {
					$scenery_brief .= "\n" . mysql_str($ppp);
				}
				$scenery_brief = trim($scenery_brief);

				$scenery_contact = $html->find('.Country_02 .jianjie ul',0);
				$scenery_contact_field = mysql_str($scenery_contact->innertext);

				$edu_form_url = "http://www.nihaowang.com/Country/{$cid}-1.html";
				$edu  = file_get_html($edu_form_url);
				$eduContent = mysql_str($edu->find('.eduContent',0)->innertext);
				$edu->clear();
// id,cid,cname,sch_count,ctitle,flag,emblem,fullname,engname,region,capital,pop,lang,brief,scenery,scenery_brief,scenery_contact,eduContent

				
				$sql = <<<EOD
insert into nh_countryv2 (id,cid,cname,sch_count,ctitle,flag,emblem,fullname,engname,region,capital,pop,lang,brief,scenery,scenery_brief,scenery_contact,eduContent,created)
values (null,{$cid},"{$cname}",$sch_count,"{$ctitle}","{$flag_field}","{$symbol_field}","{$dfullname}","{$dengname}","{$dregion}","{$dcapital}","{$dpop}","{$dlang}","{$brief}","{$scenery_field}","{$scenery_brief}","{$scenery_contact_field}","{$eduContent}",now());
EOD;
				if ($mysqli->query($sql)) {
					echo "insert {$ctitle}\t{$dfullname} done\n";
					free_mysqli();
				}else{
					print_r($mysqli->error);
					exit(1);
				}
				$html->clear();

			}
		}else{
			print_r($mysqli->error);
			exit(1);
		}
		$result->close();
		free_mysqli();

	}
	function getCommonLast($value='')
	{
		$tt = explode("：", $value);
		return mysql_str($tt[1]);
	}
	function getAllLists()
	{
		createListTables();
		global $mysqli,$country_arr,$root,$base_url;
		foreach ($country_arr as $cid => $cname) {
			$surl = str_replace("__c__", $cid, $base_url);
			$ssurl = str_replace("__p__", "1",$surl);
	 
			$thtml = file_get_html($ssurl);
			
			$sch_count = trim($thtml->find('.result span',0)->plaintext);
			$p_count   = trim($thtml->find('#bCount',0)->plaintext);
			// $country_url = $thtml->find('#aEnterCountry',0)->href;
			$country_data = trim(postData("part=GetCountryEName&cid={$cid}","http://school.nihaowang.com/Ashx/Operat.ashx"));
			$country_url = "http://www.nihaowang.com/country/" . str_replace(" ", "-", $country_data);
			//insert into country//flag,symbol,chs,eng,region,pop,capital,lang,brief)
			// insert into nh_country(id,cid,cname,sch_count,page_count,country_url)
			// values (null,{$cid},"{$cname}",{$sch_count},{$p_count},"{$country_url}");
			$sql = <<<EOD
insert into nh_country(id,cid,cname,sch_count,page_count,country_url,created)
values (null,{$cid},"{$cname}",{$sch_count},{$p_count},"{$country_url}",now());
EOD;
			if ($mysqli->query($sql)) {
				echo "$cid\t{$cname}\tStart\n";
				free_mysqli();
			}else{
				print_r($mysqli->error);
				exit(1);
			}
			$thtml->clear();

			for ($page=1; $page < $p_count; $page++) { 
				$real_url = str_replace("__p__", $page, $surl);
				$html = file_get_html($real_url);
				echo "page:$page\t$real_url\nStart!\n";
				$colleges = $html->find('.college',0)->find('.college_list,.college_list2');

				foreach ($colleges as $idx => $fl) {

					$imgdiv = $fl->find('.college_img',0);
					$one = $imgdiv->find('a',0);
					$detail_url = $one->href;
					//schid
					$sch_nid 	= basename(basename($detail_url),".html");
					//image
					$img_url	= $one->find('img',0)->getAttribute('src');
					$thumb_ret = getImage($img_url,"./nh_thumb/{$cid}/{$sch_nid}",basename($img_url));
					$thumb = $thumb_ret['error'];
					if ($thumb_ret['error'] == 0) {
						$thumb = $thumb_ret['save_path'];
					}
					//names and jiaoren
					$intro		= $fl->find('.college_intro',0);
					$titles		= $intro->find('.college_title .en_title a');
					$chs		= mysql_str($titles[0]->plaintext);
					$eng 		= mysql_str($titles[1]->plaintext);

					$jiaoren_img= $intro->find('.jiaoren_img',0);
					$jiaoren 	= 0;
					if (is_object($jiaoren_img)) {
						$jiaoren = 1;
					}

					$number = mysql_str($intro->find('.number',0)->plaintext);

					// insert into nh_schlist (id,schnid,detail_url,chs_name,eng_name,jiaoren,number,created)
					// values (null,{$sch_nic},"{$detail_url}","{$chs}","{$eng}",{$jiaoren},now());
					$sql = <<<EOD
insert into nh_schlist (id,cid,schnid,detail_url,chs_name,eng_name,jiaoren,number,thumb,created)
values (null,{$cid},{$sch_nid},"{$detail_url}","{$chs}","{$eng}",{$jiaoren},"{$number}","{$thumb}",now());
EOD;
					if ($mysqli->query($sql)) {
						echo "insert {$sch_nid}\t{$chs} done\n";
						free_mysqli();
					}else{
						print_r($mysqli->error);
						exit(1);
					}
				}
				$html->clear();

			}

		}

	}
	


	function createCountryDetailTable()
	{
		global $mysqli;
		cleanup_database('nh_countryv2');
		$create_sql = <<<EOD
create table IF NOT EXISTS `fu`.`nh_countryv2`(
	`id` int(11) unsigned primary key AUTO_INCREMENT UNIQUE,
	`cid`  int(11) unsigned,
	`cname`  varchar(255),
	`sch_count` int(11) unsigned,
	`ctitle` varchar(255),
	`flag` varchar(255),
	`emblem` varchar(255),
	`fullname` varchar(255),
	`engname` varchar(255),
	`region` varchar(255),
	`capital` varchar(255),
	`pop` varchar(255),
	`lang` varchar(255),
	`brief` text,
	`scenery` text,
	`scenery_brief` text,
	`scenery_contact` text,
	`eduContent` text,
	`created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOD;
	
		if ($mysqli->query($create_sql)) {
			free_mysqli();
		}else{
			print_r($mysqli->error);
			exit(1);
		}
	}

	function createRankListTable()
	{
		global $mysqli;
		cleanup_database('nh_ranklistv1');
		cleanup_database('nh_ranklistv2');
		cleanup_database('nh_ranklistv3');

		$_create_sql = <<<EOD
create table IF NOT EXISTS `fu`.`nh_ranklistv1`(
	`id` int(11) unsigned primary key AUTO_INCREMENT UNIQUE,
	`type` varchar(255),
	`name` varchar(255),
	`brief` text,
	`created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOD;
		if ($mysqli->query($_create_sql)) {
			free_mysqli();
		}else{
			print_r($mysqli->error);
			exit(1);
		}

		$_create_sql = <<<EOD
create table IF NOT EXISTS `fu`.`nh_ranklistv2`(
	`id` int(11) unsigned primary key AUTO_INCREMENT UNIQUE,
	`v1id` int(11) unsigned,
	`v2id` int(11) unsigned,
	`name` varchar(255),
	`year` varchar(255),
	`created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOD;
		if ($mysqli->query($_create_sql)) {
			free_mysqli();
		}else{
			print_r($mysqli->error);
			exit(1);
		}
		$_create_sql = <<<EOD
create table IF NOT EXISTS `fu`.`nh_ranklistv3`(
	`id` int(11) unsigned primary key AUTO_INCREMENT UNIQUE,
	`v2id` int(11) unsigned,
	`rid` int(11) unsigned,
	`sid` int(11) unsigned,
	`sch_name` varchar(255),
	`eng_name` varchar(255),
	`jiaoren` int(2),
	`location` varchar(255),
	`created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOD;
		if ($mysqli->query($_create_sql)) {
			free_mysqli();
		}else{
			print_r($mysqli->error);
			exit(1);
		}
		
	}

	function createSchoolDetail()
	{
		global $mysqli;
		cleanup_database('nh_schdetail');
		cleanup_database('nh_major');		

		$_create_sql = <<<EOD
create table IF NOT EXISTS `fu`.`nh_schdetail`(
	`id` int(11) unsigned primary key AUTO_INCREMENT UNIQUE,
	`cid` int(11) unsigned,
	`schnid`  int(11) unsigned,
	`chs_name` varchar(255),
	`eng_name` varchar(255),
	`jiaoren` int(1),
	`number` varchar(255),
	`thumb` varchar(255),
	`latlng` varchar(255),
	`renqi` varchar(255),
	`state` varchar(255),
	`city` varchar(255),
	`prop` varchar(255),
	`enrollrate` varchar(255),
	`build` varchar(255),
	`pop` varchar(255),
	`website` varchar(255),
	`international` varchar(255),
	`zuyi` varchar(255),
	`commonapp` varchar(10),
	`brief` text,
	`images` text,
	`major` text,
	`created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOD;
		if ($mysqli->query($_create_sql)) {
			free_mysqli();
		}else{
			print_r($mysqli->error);
			exit(1);
		}
		$_create_sql = <<<EOD
create table IF NOT EXISTS `fu`.`nh_major`(
	`id` int(11) unsigned primary key AUTO_INCREMENT UNIQUE,
	`cid` int(11) unsigned,
	`sid`  int(11) unsigned,
	`chs_name` varchar(255),
	`eng_name`  varchar(255),
	`degree_name` varchar(255),
	`address` text,
	`applyonline` varchar(255),
	`conditionage` varchar(255),
	`conditioncost` text,
	`conditionedu` varchar(255),
	`conditiontest` text,
	`conditionwork` varchar(255),
	`currency` varchar(255),
	`fax` varchar(255),
	`mail` varchar(255),
	`majorsum` varchar(255),
	`openingtime` text,
	`otherapplications` varchar(255),
	`othercondition` text,
	`otherbooks` varchar(255),
	`otherreg` varchar(255),
	`rate` varchar(255),
	`scholarshipurl` varchar(255),
	`tel` varchar(255),
	`tuition` varchar(255),
	`alimony` varchar(255),
	`xuezhi` varchar(255),
	`major` text,
	`origin` text,
	`created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOD;

		if ($mysqli->query($_create_sql)) {
			free_mysqli();
		}else{
			print_r($mysqli->error);
			exit(1);
		}
	}

	function createTanTable()
	{
		global $mysqli;
		cleanup_database('nh_tan');
		
		$create_sql = <<<EOD
create table IF NOT EXISTS `fu`.`nh_tan`(
	`id` int(11) unsigned primary key AUTO_INCREMENT UNIQUE,
	`tanid` int(11) unsigned,
	`title` varchar(255),
	`image` varchar(255),
	`timetile` varchar(255),
	`guest_name` varchar(255),
	`country` varchar(255),
	`country_eng` varchar(255),
	`school` varchar(255),
	`schid` int(11) unsigned,
	`major` varchar(255),
	`degree` varchar(255),
	`schtime` varchar(255),
	`department` varchar(255),
	`realname` varchar(255),
	`email` varchar(255),
	`qq` varchar(255),
	`other` varchar(255),
	`content` text,
	`created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOD;

		if ($mysqli->query($create_sql)) {
			free_mysqli();
		}else{
			print_r($mysqli->error);
			exit(1);
		}
	}
	function createListTables()
	{
		global $mysqli;
		cleanup_database('nh_country');
		cleanup_database('nh_schlist');
		
		$create_sql = <<<EOD
create table IF NOT EXISTS `fu`.`nh_country`(
	`id` int(11) unsigned primary key AUTO_INCREMENT UNIQUE,
	`cid`  int(11) unsigned,
	`cname`  varchar(255),
	`sch_count` int(11) unsigned,
	`page_count` int(11) unsigned,
	`country_url` varchar(255),
	`created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOD;

		if ($mysqli->query($create_sql)) {
			free_mysqli();
		}else{
			print_r($mysqli->error);
			exit(1);
		}
// id,schnid,detail_url,chs_name,eng_name,jiaoren,number,created
		$_create_sql = <<<EOD
create table IF NOT EXISTS `fu`.`nh_schlist`(
	`id` int(11) unsigned primary key AUTO_INCREMENT UNIQUE,
	`cid` int(11) unsigned,
	`schnid`  int(11) unsigned,
	`detail_url`  varchar(255),
	`chs_name` varchar(255),
	`eng_name` varchar(255),
	`jiaoren` int(1),
	`number` varchar(255),
	`thumb` varchar(255),
	`created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOD;

		if ($mysqli->query($_create_sql)) {
			free_mysqli();
		}else{
			print_r($mysqli->error);
			exit(1);
		}	

	}

	function mysql_str($value='')
	{
		global $mysqli;
		return $mysqli->real_escape_string(trim($value));
	}

	function mysql_str_json($value='')
	{
		if (!isset($value)) {
			return "";
		}
		if (is_array($value)) {
			$value = serialize($value);
		}
		return mysql_str($value);
	}

	function getImage($url,$save_dir='',$filename=''){
	    if(trim($url)==''){
			return array('file_name'=>'','save_path'=>'','error'=>1);
	    }
	    if(trim($save_dir)==''){
			$save_dir='./';
	    }
	    if(trim($filename)==''){//保存文件名
	        $ext=strrchr($url,'.');
	        if($ext!='.gif'&&$ext!='.jpg'){
				return array('file_name'=>'','save_path'=>'','error'=>3);
			}
	        $filename=time().$ext;
	    }
	    if(0!==strrpos($save_dir,'/')){
			$save_dir.='/';
	    }
	    //创建保存目录
	    if(!file_exists($save_dir)&&!mkdir($save_dir,0777,true)){
			return array('file_name'=>'','save_path'=>'','error'=>5);
	    }

	    //获取远程文件所采用的方法 
	    $ch = curl_init($url);
		$fp2 = @fopen($save_dir . $filename, 'wb');
		curl_setopt($ch, CURLOPT_FILE, $fp2);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:30.0) Gecko/20100101 Firefox/30.0');
		curl_setopt($ch, CURLOPT_REFERER, "http://school.nihaowang.com/");
		curl_exec($ch);
		curl_close($ch);
		fclose($fp2);

		if (strpos($save_dir,'tmpimg') != false) {
			$ext=strrchr($url,'.');
			$src_filename = basename($filename,$ext) . "_src" . $ext;
			@copy($save_dir . $filename,$save_dir . $src_filename);
		}
	    list($width, $height, $type, $attr) = getimagesize($save_dir.$filename);
	    if (isset($type) && in_array($type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF)))
	        return array('file_name'=>$filename,'save_path'=>$save_dir.$filename,'error'=>0);
        else
	        return array('file_name'=>$filename,'save_path'=>$save_dir.$filename,'error'=>6);
	}

	function cleanup_database($db_name)
	{
		global $mysqli;
		$sql ="DROP TABLE IF EXISTS `fu`.`{$db_name}`;";
		if ($mysqli->multi_query($sql)) {
			free_mysqli();
		}
	}


	function free_mysqli()
	{
		global $mysqli;
		while($mysqli->more_results())
		{
		    $mysqli->next_result();
		    if($res = $mysqli->store_result()) // added closing bracket
		    {
		        $res->free(); 
		    }
		}
	}



	function postData($post='',$url='',$need_login=false)
	{
		$cookie_jar = "/tmp/nihao";
		if ($need_login) {
			include_once('simple_html_dom.php');
			$login = "http://www.nihaowang.com/API/UserOpration.ashx?jsoncallback=?jsoncallback=jQuery16026542413560673594_1406277057557&action=login1&username=username&pwd=password&mob=&code=&_=1406277081472";	
			$chx = curl_init($login);
			curl_setopt($chx, CURLOPT_HEADER, 0);
			curl_setopt($chx, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($chx, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($chx, CURLOPT_REFERER, "http://school.nihaowang.com/");
			$ret = curl_exec($chx);
			curl_close($chx);
			$ret = trim($ret);
			$ret = str_replace("?jsoncallback=jQuery16026542413560673594_1406277057557(", "", $ret);
			$ret = rtrim($ret,")");

			$jj = json_decode($ret);
	        if (isset($jj->js)) {
	            $cs = $jj->js;
	            $tt = str_get_html($cs);
	            if (is_object($tt)) {
	                $cc = $tt->find('script',0)->getAttribute('src');
	                $chx = curl_init($cc);
	                curl_setopt($chx, CURLOPT_HEADER, 0);
	                curl_setopt($chx, CURLOPT_RETURNTRANSFER, true);
	                curl_setopt($chx, CURLOPT_COOKIEJAR, $cookie_jar);
	                curl_exec($chx);
	                curl_close($chx);
	            }
	            $tt->clear();
	        }
		}
		

		$ch = curl_init( $url );
		curl_setopt( $ch, CURLOPT_POST, 1);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt( $ch, CURLOPT_HEADER, 0);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
		if ($need_login) {
    		curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookie_jar);
    	}
		curl_setopt( $ch, CURLOPT_REFERER, "http://school.nihaowang.com/");
		$response = curl_exec( $ch );
		curl_close($ch);
		return $response;
	}
?>
