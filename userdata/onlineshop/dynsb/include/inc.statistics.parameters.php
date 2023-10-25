<?php
/**
 * Table, which contains elements to adjust diagram
 *
 * !if using class.pagestatistics.php, you should include
 * inc.statistics.parameters.forobject.php !
 *
 *
 */
?>
 <table>
  <tr>
    <td align="right"><?php echo L_dynsb_ImageSize;?>:&nbsp;</td>
    <td>
	    <select name="picsize">
	        <option value="400x100" <?php if($picsize=='400x100') echo "selected";?>><?php echo "400 x 100 ".L_dynsb_Pixel;?></option>
	        <option value="500x180" <?php if($picsize=='500x180') echo "selected";?>><?php echo "500 x 180 ".L_dynsb_Pixel;?></option>
	        <option value="630x240" <?php if($picsize=='630x240') echo "selected";?>><?php echo "630 x 240 ".L_dynsb_Pixel;?></option>
	        <option value="630x630" <?php if($picsize=='630x630') echo "selected";?>><?php echo "630 x 630 ".L_dynsb_Pixel;?></option>
	    </select>
	    <input type="hidden" value="<?php echo $xsize;?>" name="xsize">
	    <input type="hidden" value="<?php echo $ysize;?>" name="ysize">
    </td>
    <td align="right"><?php echo L_dynsb_StartDate;?>:&nbsp;</td>
    <td>
    	<input type="text" maxlength="32" value="<?php echo timestamp_mysql2german($statStartDate);?>" name="statStartDate" id="statStartDate" readonly>&nbsp;
    	<img src="../../../../image/calendar.gif" id="statStartDateTrigger" style="cursor: pointer" alt="<?php echo L_dynsb_Calendar;?>" title="<?php echo L_dynsb_Calendar;?>">
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
    <td>
    <select name="layout">
        <option value="0" <?php if($layout == 0) echo "selected";?>><?php echo L_dynsb_Horizontal;?></option>
        <option value="1" <?php if($layout == 1) echo "selected";?>><?php echo L_dynsb_Vertical;?></option>
    </select>
    </td>
    <td align="right"><?php echo L_dynsb_EndDate;?>:&nbsp;</td>
    <td>
    	<input type="text" maxlength="32" value="<?php echo timestamp_mysql2german($statEndDate);?>" name="statEndDate" id="statEndDate" readonly>&nbsp;
    	<img src="../../../../image/calendar.gif" id="statEndDateTrigger" style="cursor: pointer" alt="<?php echo L_dynsb_Calendar;?>" title="<?php echo L_dynsb_Calendar;?>">
    	<script language="JavaScript" type="text/javascript">
			Calendar.setup(
			            {
			        inputField	 :    "statEndDate",
			        ifFormat     :    "%d.%m.%Y",
			                button       :    "statEndDateTrigger",
			        showsTime	 :    false,
			        singleClick	 :    true,
			        firstDay	 :	  1,
			        align        :    "Bl"
			    });
			</script>
    </td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_BarLayout;?>:&nbsp;</td>
    <td>
    <select name="barlayout">
        <option value="0" <?php if($barlayout==0) echo "selected";?>><?php echo L_dynsb_SimpleShaded;?></option>
        <option value="1" <?php if($barlayout==1) echo "selected";?>><?php echo L_dynsb_Gradient;?></option>
    </select>
    </td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_Result;?>:&nbsp;</td>
    <td>
    <select name="viewmode">
        <option value="0" <?php if($viewmode==0) echo "selected";?>><?php echo L_dynsb_Top10;?></option>
        <option value="1" <?php if($viewmode==1) echo "selected";?>><?php echo L_dynsb_Top20;?></option>
        <option value="2" <?php if($viewmode==2) echo "selected";?>><?php echo L_dynsb_Top50;?></option>
    </select>
    </td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>

  <!--START choose period of time-->
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
			echo "<option value='". date("d.m.Y",mktime()-mktime(1,0,0,1,8,1970))."' >
			".L_dynsb_statOneWeek." </option>";

			//1 month
			echo "<option value='". date("d.m.Y",mktime()-mktime(1,0,0,2,1,1970))."' >
			".L_dynsb_statOneMonth."</option>";

			//3 month
			echo "<option value='". date("d.m.Y",mktime()-mktime(1,0,0,4,1,1970))."' >
			".L_dynsb_statThreeMonths." </option>";

			//6month
			echo "<option value='". date("d.m.Y",mktime()-mktime(1,0,0,7,1,1970))."' >
			".L_dynsb_statSixMonths." </option>";

			//1year
			echo "<option value='". date("d.m.Y",mktime()-mktime(1,0,0,1,1,1971))."' >
			".L_dynsb_statOneYear." </option>";

			//1year
			echo "<option value='". date("d.m.Y",mktime()-mktime(1,0,0,1,1,1999))."' >
			".L_dynsb_statAll." </option>";
	?>
	    </select>
		</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
<!--END choose period of time-->

  <tr>
    <td align="right">&nbsp;</td>
    <td>
        <input type="button" class="button" onclick="javascript:refreshLayout();" name="btn_refresh1" value="<?php echo L_dynsb_Refresh;?>">
    </td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>