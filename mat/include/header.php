<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>MAT</title>
    <link rel="icon" href="data:image/x-icon;base64,AAABAAEAEBAAAAAAAABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAJAAAAFgAAABoAAAAaAAAAGgAAABoAAAAaAAAAGgAAABoAAAAaAAAAGgAAABoAAAAaAAAAGgAAABYAAAAJAAAAEgEOADMCSgCDAl0AvAJdAMwCXQDMAl0AzAJdAMwCXQDMAl0AzAJdAMwCXQDMAl0AvAJKAIMBDgAzAAAAEgIdAAAGbQBzEpII3SDMEPki2RH/ItkR/yLZEf8i2RH/ItkR/yLZEf8i2RH/ItkR/x/MD/kQkgfdBm0AcwIdAAAKfQAACn0AuiXKFfki0RH/ItER/yLREf8i0RH/IrYR/yK2Ef8i0RH/ItER/yLREf8i0RH/IMgP+Qp9ALoKfQAADIQAAAyEAMwrzBr/IsgR/yLIEf8iyBH/IrwR/+jo6P/s7Oz/IrwR/yLIEf8iyBH/IsgR/yLIEf8MhADMDIQAAA2JAAANiQDMMcYg/yK+Ef8ivhH/Ir4R/yK1Ef/k5OT/6Ojo/yK1Ef8ivhH/Ir4R/yK+Ef8jvhL/DYkAzA2JAAAOjQAADo0AzEHDMP8jtBL/IqgR/yKoEf8ipBH/4ODg/+Tk5P8ipBH/IqgR/yKoEf8itBH/JbUU/w6NAMwOjQAAD5IAAA+SAMxSyUH/M68i/9TU1P/T09P/19fX/9zc3P/g4OD/5OTk/+jo6P/s7Oz/IqYR/yivF/8PkgDMD5IAABCWAAAQlgDMVcxE/zyzK//4+Pj/4eHh/9XV1f/X19f/3Nzc/+Dg4P/k5OT/6Ojo/yKgEf8sqhv/EJYAzBCWAAARmgAAEZoAzFrRSf9Hvjb/PrUt/z61Lf83rib/6+vr/+Li4v8lnRT/I5sS/yObEv8nnxb/ObEo/xGaAMwRmgAAEp4AABKeAMxg10//TsU9/07FPf9OxT3/RLsz////////////RLsz/07FPf9OxT3/TsU9/1jPR/8SngDMEp4AABOiAAATogDMZ95W/1fORv9Xzkb/V85G/0rBOf///////////0rBOf9Xzkb/V85G/1fORv9g10//E6IAzBOiAAAUpQAAFKUAumTeU/lf1k7/X9ZO/1/WTv9f1k7/UMc//1DHP/9f1k7/X9ZO/1/WTv9f1k7/YNpP+RSlALoUpQAAFKgAABSoAHM3wSTdZuBU+W7lXf9u5V3/buVd/27lXf9t5Fz/beRc/23kXP9t5Fz/ZN9T+Ta/I90UqABzFKgAABSoAAAVqQAMFaoAcxWqALoVqgDMFaoAzBWqAMwVqgDMFaoAzBWqAMwVqgDMFaoAzBWqALoVqgBzFakADBSoAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//8AAMADAADAAwAAgAEAAIABAACAAQAAgAEAAIABAACAAQAAgAEAAIABAACAAQAAgAEAAMADAADgBwAA//8AAA==">
    <link rel="stylesheet" href="mat.css" type="text/css">
    <script type="text/javascript">
			// <!--
//<![CDATA[
function start() {
  var elems = document.getElementsByClassName('elements');
  var labels = document.getElementsByClassName('label');
  hideAll();
  for (var i = 0; i < labels.length; i++) {
    labels[i].onclick = toggleBlock;
  }
  document.getElementById('defaultdifficulty').checked=true;
}
function hideAll() {
  var elems = document.getElementsByClassName('elements');
  for (var i = 0; i < elems.length; i++) {
    elems[i].style.display = 'none';
  }
}
function toggleBlock(evnt) {
  itemid = "elements" + evnt.target.id.substr(5, 1);
  item = document.getElementById(itemid);
  if (item.style.display=='none') {
    hideAll();
    item.style.display = 'block';
  }
}
//]]>
			// -->
		</script>
  </head>
  <body onload="start();">
