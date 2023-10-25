<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]

if($this->get_setting('cb_shippingTab_Checked') == 'True')
{
	$cSymbol = $this->get_setting('edCurrencySymbol_Text');
	$cUnit = $this->get_setting('edWeightUnit_Text');
	$shipmenthtml = file_get_contents('template/shipmenttable.html');
	$aTags = $this->get_tags_ret($shipmenthtml);
	$shipmenthtml = $this->parse_texts($aTags,$shipmenthtml);
	
	$shipmentareashtml = file_get_contents('template/shipmentareas.html');
	$aSATags = $this->get_tags_ret($shipmentareashtml);
	$shipmentareashtml = $this->parse_texts($aSATags,$shipmentareashtml);
	
	$shipmentoneareahtml = file_get_contents('template/shipmentonearea.html');
	
	$shipmentareacostshtml = file_get_contents('template/shipmentareacosts.html');
	$shipmentareacostshtml1 = file_get_contents('template/shipmentareacosts1.html');
	$shipmentareacostshtml2 = file_get_contents('template/shipmentareacosts2.html');
	$aSCTags = $this->get_tags_ret($shipmentareacostshtml);
	$aSCTags1 = $this->get_tags_ret($shipmentareacostshtml1);
	$aSCTags2 = $this->get_tags_ret($shipmentareacostshtml2);
	$shipmentareacostshtml = $this->parse_texts($aSCTags,$shipmentareacostshtml);
	$shipmentareacostshtml1 = $this->parse_texts($aSCTags1,$shipmentareacostshtml1);
	$shipmentareacostshtml2 = $this->parse_texts($aSCTags2,$shipmentareacostshtml2);
	
	$dbh = $this->db_connect();
	//Alle Versandgebiete
	$asql = "SELECT AreaId, Text FROM ".$this->dbtoken."addressarea WHERE Text != '' AND LanguageId = '" . $this->lngID . "' AND CountryId = '" . $this->cntID . "' ORDER BY AreaId ASC";
	$aerg = mysqli_query($dbh,$asql);
	if(mysqli_errno($dbh) == 0)
	{
		$spmAreas = '';
		while($a = mysqli_fetch_assoc($aerg))
		{
			$cur_area = $shipmentareashtml;
			$cur_area = str_replace('{GSSE_INCL_ShipmentAdressArea}',$a['Text'], $cur_area);
			//Alle Versandarten in dem Gebiet
			$aosql = "SELECT *, " .
						"(SELECT ShippingName FROM ".$this->dbtoken."deliverylanguage WHERE ".$this->dbtoken."deliverylanguage.SortId = ".$this->dbtoken . "deliveryarea.SortId) AS devName" .
						" FROM ".$this->dbtoken."deliveryarea WHERE AddressArea = '" . $a['AreaId'] . "' AND CountryId = '" . $this->cntID . "' ORDER BY SortId ASC";
			$aoerg = mysqli_query($dbh,$aosql);
			if(mysqli_errno($dbh) == 0)
			{
				$oneArea = '';
				
				while($ao = mysqli_fetch_assoc($aoerg))
				{
					$areaCosts = '';
					$cur_oneArea = $shipmentoneareahtml;
					$cur_Areacost = $shipmentareacostshtml;
					$cur_Areacost1 = $shipmentareacostshtml1;
					$cur_Areacost2 = $shipmentareacostshtml2;
					$cur_oneArea = str_replace('{GSSE_INCL_ShipmentOneArea}',$ao['devName'], $cur_oneArea);
					//Kosten für Gewicht 0 - 
					$cur_Areacost = str_replace('{GSSE_INCL_LANGSHIPMWEIGHTFROM}','',$cur_Areacost);
					$cur_Areacost = str_replace('{GSSE_INCL_SHIPMENTWEIGHTFROM}','',$cur_Areacost);
					$cur_Areacost = str_replace('{GSSE_INCL_SHIPMENTWEIGHTUNIT}','',$cur_Areacost);
					$cur_Areacost = str_replace('{GSSE_INCL_CURRENCY}',$cSymbol,$cur_Areacost);
					$cur_Areacost1 = str_replace('{GSSE_INCL_CURRENCY}',$cSymbol,$cur_Areacost1);
					$cur_Areacost2 = str_replace('{GSSE_INCL_CURRENCY}',$cSymbol,$cur_Areacost2);
					$cur_Areacost = str_replace('{GSSE_INCL_SHIPMENTWEIGHTCOST}',$ao['ShippingCost'],$cur_Areacost);
					
					if(($ao['FromInvoiceAmount1']>0) && ($ao['MaxShippingCharge1']>0)){
						$cur_Areacost1 = str_replace('{GSSE_INCL_SHIPMENTVALUE1}',$ao['FromInvoiceAmount1'],$cur_Areacost1);
						$cur_Areacost1 = str_replace('{GSSE_INCL_SHIPMENTCOST1}',$ao['MaxShippingCharge1'],$cur_Areacost1);
						$cur_Areacost = str_replace('{GSSE_INCL_SHIPMENTAREACOST1}',$cur_Areacost1,$cur_Areacost);
					} else {
						$cur_Areacost = str_replace('{GSSE_INCL_SHIPMENTAREACOST1}','',$cur_Areacost);
					}
					if(($ao['FromInvoiceAmount2']>0) && ($ao['MaxShippingCharge2']>0)){
						$cur_Areacost2 = str_replace('{GSSE_INCL_SHIPMENTVALUE2}',$ao['FromInvoiceAmount2'],$cur_Areacost2);
						$cur_Areacost2 = str_replace('{GSSE_INCL_SHIPMENTCOST2}',$ao['MaxShippingCharge2'],$cur_Areacost2);
						$cur_Areacost = str_replace('{GSSE_INCL_SHIPMENTAREACOST2}',$cur_Areacost2,$cur_Areacost);
					} else {
						$cur_Areacost = str_replace('{GSSE_INCL_SHIPMENTAREACOST2}','',$cur_Areacost);
					}	
					$areaCosts .= $cur_Areacost;
					
					//Kosten für verschiedene Gewichte
					$awsql = "SELECT * FROM ".$this->dbtoken."deliverycountry WHERE SortId = '" . $ao['SortId'] . "' AND CountryId = '" . $this->cntID . "' ORDER BY ShippingToWeight ASC";
					$awerg = mysqli_query($dbh,$awsql);
					if(mysqli_errno($dbh) == 0)
					{
						while($aw = mysqli_fetch_assoc($awerg))
						{
							$cur_Areacost = $shipmentareacostshtml;
							$cur_Areacost1 = $shipmentareacostshtml1;
							$cur_Areacost2 = $shipmentareacostshtml2;
							$cur_Areacost = str_replace('{GSSE_INCL_LANGSHIPMWEIGHTFROM}',$this->get_lngtext('LangTagWeightFrom'),$cur_Areacost);
							$cur_Areacost = str_replace('{GSSE_INCL_SHIPMENTWEIGHTFROM}',$aw['ShippingToWeight'],$cur_Areacost);
							$cur_Areacost = str_replace('{GSSE_INCL_SHIPMENTWEIGHTUNIT}',$cUnit,$cur_Areacost);
							$cur_Areacost = str_replace('{GSSE_INCL_CURRENCY}',$cSymbol,$cur_Areacost);
							$cur_Areacost1 = str_replace('{GSSE_INCL_CURRENCY}',$cSymbol,$cur_Areacost1);
							$cur_Areacost2 = str_replace('{GSSE_INCL_CURRENCY}',$cSymbol,$cur_Areacost2);
							$cur_Areacost = str_replace('{GSSE_INCL_SHIPMENTWEIGHTCOST}',$aw['ShippingCost'],$cur_Areacost);
							if(($ao['FromInvoiceAmount1']>0) && ($ao['MaxShippingCharge1']>0)){
								$cur_Areacost1 = str_replace('{GSSE_INCL_SHIPMENTVALUE1}',$aw['FromInvoiceAmount1'],$cur_Areacost1);
								$cur_Areacost1 = str_replace('{GSSE_INCL_SHIPMENTCOST1}',$aw['MaxShippingCharge1'],$cur_Areacost1);
								$cur_Areacost = str_replace('{GSSE_INCL_SHIPMENTAREACOST1}',$cur_Areacost1,$cur_Areacost);
							} else {
								$cur_Areacost = str_replace('{GSSE_INCL_SHIPMENTAREACOST1}','',$cur_Areacost);
							}	
							if(($ao['FromInvoiceAmount2']>0) && ($ao['MaxShippingCharge2']>0)){	
								$cur_Areacost2 = str_replace('{GSSE_INCL_SHIPMENTVALUE2}',$aw['FromInvoiceAmount2'],$cur_Areacost2);
								$cur_Areacost2 = str_replace('{GSSE_INCL_SHIPMENTCOST2}',$aw['MaxShippingCharge2'],$cur_Areacost2);
								$cur_Areacost = str_replace('{GSSE_INCL_SHIPMENTAREACOST2}',$cur_Areacost2,$cur_Areacost);
							} else {
								$cur_Areacost = str_replace('{GSSE_INCL_SHIPMENTAREACOST2}','',$cur_Areacost);
							}	
								
							$areaCosts .= $cur_Areacost;
						}
						mysqli_free_result($awerg);
					}
					
					
					//Gesamte Kostentabelle für Gebiet platzieren
					
					$cur_oneArea = str_replace('{GSSE_INCL_SHIPMENTAREACOSTS}',$areaCosts, $cur_oneArea);
					$oneArea .= $cur_oneArea;
				}
				mysqli_free_result($aoerg);
			}
			$cur_area = str_replace('{GSSE_INCL_SHIPMENTONEAREA}',$oneArea, $cur_area);
			$spmAreas .= $cur_area;
		}
	}
	
	$shipmenthtml = str_replace('{GSSE_INCL_SHIPMENTAREAS}',$spmAreas,$shipmenthtml);
	
}
else
{
	$shipmenthtml = '';
}
$this->content = str_replace($tag, $shipmenthtml, $this->content);
?>
