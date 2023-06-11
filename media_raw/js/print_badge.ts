jQuery(function () {
  jQuery('.badgenamelandscape').each(function (index, value) {
    var height = value.getBoundingClientRect().height; // height because we will rotate
    var scaleFactor = 1;

    var dpr = window.devicePixelRatio;
    var middle = -43.4 * 3.8 * dpr; // 3.8 = 96pixel/inch * 1 inch/25.4mm
    var maxWidth = 48 * 3.8 * dpr; // less than 51.2mm 

    if ( height > maxWidth ) {
      scaleFactor = maxWidth/height;
    }
    
    var bottom = middle - (height * scaleFactor / 2)

    var t = "rotate(-90deg) translate(" + bottom + "px,9mm) scaleX(" + scaleFactor + ")";
    jQuery(this).css("transform", t);
  });

  jQuery('.badgenameportrait').each(function (index, value) {
    var width = value.getBoundingClientRect().width;
    var scale = '';
    var left = 10;

    if (width > 170) {
      var s = 170 / width;
      scale = "scaleX(" + s + ")";
    } else {
      left = left + (170 - width) / 2;
    }

    var t = "translate(" + left + "px,20mm) " + scale;
    jQuery(this).css("transform", t);
  });

});