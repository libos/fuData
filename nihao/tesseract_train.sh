#!/bin/sh
# before use tesseract -l chi_sim $name.tif $name  batch.nochop makebox
name=$1
echo "tesseract $name.tif $name nobatch box.train"
tesseract $name.tif $name nobatch box.train
echo "unicharset_extractor $name.box"
unicharset_extractor $name.box
echo "UnknownFont 0 0 0 0 0" > font_properties
echo "mftraining -F font_properties -U unicharset $name.tr"
mftraining -F font_properties -U unicharset $name.tr
echo "cntraining $name.tr"
cntraining $name.tr | grep Error
echo "Rename File..."
mv inttemp $name.inttemp
mv normproto $name.normproto
mv pffmtable $name.pffmtable
mv shapetable $name.shapetable
mv unicharset $name.unicharset
cp font_properties $name.font_properties
echo "combine_tessdata $name."
combine_tessdata $name.
echo "\nDone!\ncp $name.traineddata /usr/share/tesseract-ocr/tessdata/"
echo "tesseract -l chi_sim+$name $name.tif $name"

