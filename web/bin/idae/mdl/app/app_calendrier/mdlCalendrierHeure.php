<?
	if (!empty($_POST['form'])) {
		$inputString = "$('" . $_POST['form'] . "')." . $_POST['input'];
	}
	if (empty($_POST['form'])) {
		$inputString = "$('" . $_POST['input'] . "')";
	}
	$inputString = "$('" . $_POST['input'] . "')";
?>
<div style="width:240px;height:162px;" class="border4 blanc">
	<div class="blanc" style="width:100%;left:1px;">
		<div class="borderB mainGui" style="padding:2px; width:auto;">
			<a onClick="fillInputForm('<?= $form ?>','<?= $input ?>','00');fillInputForm('<?= $form ?>','type<?= $form ?>','Evenement matinée');return false;"><font color="#0000FF">PM</font></a>
			&nbsp;
			|&nbsp;
			<a onClick="fillInputForm('<?= $form ?>','<?= $input ?>','12');fillInputForm('<?= $form ?>','type<?= $form ?>','Evenement après-midi');return false;"><font color="#CC6633">AM</font></a>
			<strong>Choisir</strong>
			<span id="spyChooseNbre"></span>
		</div>
		<table class="infos10 cursor" width="100%" cellpadding="0" cellspacing="0">
			<tr>
				<?
					for ($hr = 7; $hr < 14; $hr++) {
						if ($hr == 12) {
							$clf = ' hMidi';
						} else {
							$clf = '';
						}
						if ($hr < 8) {
							$clf = ' hMidi';
						}
						if ($hr > 18) {
							$clf = ' hMidi';
						}
						?>
						<td onMouseOver="$('spyChooseNbre').innerHTML='<?= $hr . ':00:00' ?>';" class="mainGui <?= $clf ?>" onClick="fillInput(<?= $inputString ?>,'<?= $hr . ':00:00' ?>');" nowrap="nowrap"><strong>
								<?= $hr ?>
							</strong>H
						</td>
					<? } ?>
			</tr>
			<tr>
				<?
					for ($hr = 7; $hr < 14; $hr++) {
						if ($hr == 12) {
							$clf = ' hMidi';
						} else {
							$clf = '';
						}
						if ($hr < 8) {
							$clf = ' hMidi';
						}
						if ($hr > 18) {
							$clf = ' hMidi';
						}
						?>
						<td onMouseOver="$('spyChooseNbre').innerHTML='<?= $hr . ':15:00' ?>';" class="<?= $clf ?>" align="right" valign="middle" nowrap="nowrap" onClick="fillInput(<?= $inputString ?>,'<?= $hr . ':15:00' ?>');">15</td>
					<? } ?>
			</tr>
			<tr>
				<?
					for ($hr = 7; $hr < 14; $hr++) {
						?>
						<td onMouseOver="$('spyChooseNbre').innerHTML='<?= $hr . ':30:00' ?>';" align="right" valign="middle" nowrap="nowrap" onClick="fillInput(<?= $inputString ?>,'<?= $hr . ':30:00' ?>');">30</td>
					<? } ?>
			</tr>
			<tr>
				<?
					for ($hr = 7; $hr < 14; $hr++) {
						?>
						<td onMouseOver="$('spyChooseNbre').innerHTML='<?= $hr . ':45:00' ?>';" align="right" valign="middle" nowrap="nowrap" onClick="fillInput(<?= $inputString ?>,'<?= $hr . ':45:00' ?>');">45</td>
					<? } ?>
			</tr>
			<tr>
				<?
					for ($hr = 14; $hr < 21; $hr++) {
						?>
						<td onMouseOver="$('spyChooseNbre').innerHTML='<?= $hr . ':00:00' ?>';" class="mainGui" onClick="fillInput(<?= $inputString ?>,'<?= $hr . ':00:00' ?>');" nowrap="nowrap"><strong>
								<?= $hr ?>
							</strong>H
						</td>
					<? } ?>
			</tr>
			<tr>
				<?
					for ($hr = 14; $hr < 21; $hr++) {
						?>
						<td onMouseOver="$('spyChooseNbre').innerHTML='<?= $hr . ':15:00' ?>';" align="right" valign="middle" nowrap="nowrap" onClick="fillInput(<?= $inputString ?>,'<?= $hr . ':15:00' ?>');">15</td>
					<? } ?>
			</tr>
			<tr>
				<?
					for ($hr = 14; $hr < 21; $hr++) {
						?>
						<td onMouseOver="$('spyChooseNbre').innerHTML='<?= $hr . ':30:00' ?>';" align="right" valign="middle" nowrap="nowrap" onClick="fillInput(<?= $inputString ?>,'<?= $hr . ':30:00' ?>');">30</td>
					<? } ?>
			</tr>
			<tr>
				<?
					for ($hr = 14; $hr < 21; $hr++) {
						?>
						<td onMouseOver="$('spyChooseNbre').innerHTML='<?= $hr . ':45:00' ?>';" align="right" valign="middle" nowrap="nowrap" onClick="fillInput(<?= $inputString ?>,'<?= $hr . ':45:00' ?>');">45</td>
					<? } ?>
			</tr>
		</table>
	</div>
</div>
<script>
	fillInput = function (input, vars) {
		$ (input).value = vars;
	}
</script>
