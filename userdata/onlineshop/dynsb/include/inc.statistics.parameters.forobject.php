<?php
/**
 * Page is the same like inc.statistics.page.php except it is used for statistics
 * that make use of class.pagestatistics.php
 *
 */

?>

 <table>
  <tr>
    <td align="right"><?php echo L_dynsb_ImageSize;?>:&nbsp;</td>

    <!-- picture size chooser -->
    <td>
	    <select name="picsize">
	<?php
				//Get available diagram sizes
				$diagramSizes=$ps->getDiagramSizes();

				for ($i=0;$i< count($diagramSizes);$i++)
				{
					// to keep a value selected
					if ($picsize==$i){$sel=" selected ";} else {$sel=" ";}

					//Generate options
					echo "<option value='$i' $sel> $diagramSizes[$i] ".L_dynsb_Pixel."</option>";
				}
	?>
			</select>

    </td>
 <!-- Start date -->
    <td align="right">
      <?php echo L_dynsb_StartDate;?>:&nbsp;
    </td>

 <!--start date chooser -->
    <td>
     <input type="text" maxlength="32" value="<?php echo timestamp_mysql2german($statStartDate);?>" name="statStartDate" id="statStartDate" readonly>&nbsp;
     <img src="../../../../image/calendar.gif" id="statStartDateTrigger" style="cursor: pointer" title="<?php echo L_dynsb_Calendar;?>" alt="<?php echo L_dynsb_Calendar;?>">
	    <script language="JavaScript" type="text/javascript">
				Calendar.setup(
				{
	        inputField	 :    "statStartDate",
	        ifFormat     :    "%d.%m.%Y",
					button       :    "statStartDateTrigger",
	        showsTime	 :    false,
	        singleClick	 :    true,
	        firstDay	 :	  1,
	        align        :    "Bl"
		    });
			</script>
    </td>
  </tr>

	<tr>
    <td align="right"><?php echo L_dynsb_PictureLayout;?>:&nbsp;</td>

     <!-- picture format (layout)-->
    <td>
    <select name="layout">
        <option value="0" <?php if($layout==0) echo "selected"?>><?php echo L_dynsb_Horizontal;?></option>
        <option value="1" <?php if($layout==1) echo "selected"?>><?php echo L_dynsb_Vertical;?></option>
    </select>
    </td>

		<!-- end date-->
    <td align="right"><?php echo L_dynsb_EndDate;?>:&nbsp;</td>

		<!-- end date chooser-->
    <td>
    	<input type="text" maxlength="32" value="<?php echo timestamp_mysql2german($statEndDate)?>" name="statEndDate" id="statEndDate" readonly>
    	<img src="../../../../image/calendar.gif" id="statEndDateTrigger" style="cursor: pointer" title="<?php echo L_dynsb_Calendar;?>" alt="<?php echo L_dynsb_Calendar;?>">
	    <script language="JavaScript" type="text/javascript">
				Calendar.setup(
				{
		        inputField	 :    "statEndDate",
		        ifFormat     :    "%d.%m.%Y",
						button       :    "statEndDateTrigger",
		        showsTime	 	 :    false,
		        singleClick	 :    true,
		        firstDay	 	 :	  1,
		        align        :    "Bl"
				});
			</script>
    </td>
  </tr>

  <tr>
    <!-- bar layout-->
    <td align="right"><?php echo L_dynsb_BarLayout;?>:&nbsp;</td>

    <!-- bar layout chooser-->
    <td>
    <select name="barlayout">
        <option value="0" <?php if($barlayout==0) echo "selected";?>><?php echo L_dynsb_SimpleShaded?></option>
        <option value="1" <?php if($barlayout==1) echo "selected";?>><?php echo L_dynsb_Gradient;?></option>
    </select>
    </td>

    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>

  <tr>
		<!-- analysis-->
		<td align="right"><?php echo L_dynsb_Result;?>:&nbsp;</td>

		<!-- analysis chooser-->
    <td>
    	<select name="userdetailmode">
<?php
			//Get available modes
			$diagramViewmodes=$ps->getUserDetailModes();

			for ($i=0;$i< count($diagramViewmodes);$i++)
			{
		  	// to keep a value selected
				if ($viewmode == $i){$sel=" selected ";} else {$sel=" ";}

				//Generate options
				echo "<option value='$i' $sel>$diagramViewmodes[$i]</option>";
			}
?>
    	</select>
    </td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>

   <tr>
    <td align="right">&nbsp;<?php echo L_dynsb_statPeriod?>:&nbsp;</td>

    <!-- choose period of time-->
    <td>
	    <select name="datechooser"
	    		onchange="document.getElementById('statStartDate').value=datechooser.value;
	    		  				document.getElementById('statEndDate').value='<?php echo date("d.m.Y")?>';">

			<option value='<?php echo date("d.m.Y")?>'selected><?php echo L_dynsb_statChooseTime; ?></option>
	<?php
			//1 week
			echo 	"<option value='". date("d.m.Y",mktime()-mktime(1,0,0,1,8,1970))."' >
						".L_dynsb_statOneWeek." </option>";

			//1 month
			echo 	"<option value='". date("d.m.Y",mktime()-mktime(1,0,0,2,1,1970))."' >
						".L_dynsb_statOneMonth."</option>";

			//3 month
			echo 	"<option value='". date("d.m.Y",mktime()-mktime(1,0,0,4,1,1970))."' >
						".L_dynsb_statThreeMonths." </option>";

			//6month
			echo 	"<option value='". date("d.m.Y",mktime()-mktime(1,0,0,7,1,1970))."' >
						".L_dynsb_statSixMonths." </option>";

			//1year
			echo 	"<option value='". date("d.m.Y",mktime()-mktime(1,0,0,1,1,1971))."' >
						".L_dynsb_statOneYear." </option>";

			//1year
			echo 	"<option value='". date("d.m.Y",mktime()-mktime(1,0,0,1,1,1999))."' >
						".L_dynsb_statAll." </option>";
	?>
	    </select>
		</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
	</tr>

  <tr>
    <td align="right">&nbsp;</td>

    <!-- refresh button-->
    <td>
    	<input type="submit" class="button" name="refreshLayoutButton" value="<?php echo L_dynsb_Refresh;?>">
    </td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>