<?php 
	if (!isset($argv[1])) 
	{
		echo "Error: use `php {$argv[0]} file_base_name`";
	}
	$name = $argv[1];
	$parts = explode(".", $name);

	echo "tesseract $name.tif $name nobatch box.train\n";
	exec("tesseract $name.tif $name nobatch box.train");
	echo "unicharset_extractor $name.box\n";
	exec("unicharset_extractor $name.box");
	exec("echo '{$parts[1]} 0 0 0 0 0' > font_properties");

	echo "mftraining -F font_properties -U unicharset $name.tr\n";
	exec("mftraining -F font_properties -U unicharset $name.tr");

	echo "cntraining $name.tr\n";
	exec("cntraining $name.tr");

	echo "Rename File...\n";
	exec("mv inttemp {$parts[0]}.inttemp");
	exec("mv normproto {$parts[0]}.normproto");
	exec("mv pffmtable {$parts[0]}.pffmtable");
	exec("mv shapetable {$parts[0]}.shapetable");
	exec("mv unicharset {$parts[0]}.unicharset");
	exec("mv font_properties {$parts[0]}.font_properties");

	echo "combine_tessdata {$parts[0]}.\n";
	exec("combine_tessdata {$parts[0]}.");

	echo "\nDone!\ncp {$parts[0]}.traineddata /usr/share/tesseract-ocr/tessdata/\n";
	echo "tesseract -l chi_sim+$name $name.tif output\n";

?>