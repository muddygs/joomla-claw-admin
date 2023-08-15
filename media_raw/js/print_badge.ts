document.addEventListener("DOMContentLoaded", function () {
  const badgesLandscape = document.querySelectorAll(".badgenamelandscape");
  badgesLandscape.forEach((badge) => {
    const height = badge.getBoundingClientRect().height;
    let scaleFactor = 1;

    const dpr = window.devicePixelRatio;
    const middle = -43.4 * 3.8 * dpr;
    const maxWidth = 48 * 3.8 * dpr;

    if (height > maxWidth) {
      scaleFactor = maxWidth / height;
    }

    const bottom = middle - height * scaleFactor / 2;

    const t = `rotate(-90deg) translate(${bottom}px,9mm) scaleX(${scaleFactor})`;
    badge.setAttribute("style", t);
  });

  const badgesPortrait = document.querySelectorAll(".badgenameportrait");
  badgesPortrait.forEach((badge) => {
    const width = badge.getBoundingClientRect().width;
    let scale = "";
    let left = 10;

    if (width > 170) {
      const s = 170 / width;
      scale = `scaleX(${s})`;
    } else {
      left = left + (170 - width) / 2;
    }

    const t = `translate(${left}px,20mm) ${scale}`;
    badge.setAttribute("style", t);
  });
});