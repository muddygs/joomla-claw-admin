document.addEventListener("DOMContentLoaded", function () {
  const badgesLandscape = document.querySelectorAll(".landscape .badgename");
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

    const t = `transform: rotate(-90deg) translate(${bottom}px,9mm) scaleX(${scaleFactor});`;
    badge.setAttribute("style", t);
  });

  const badgesPortrait = document.querySelectorAll(".portrait .badgename");
  badgesPortrait.forEach((badge) => {
    const width = badge.getBoundingClientRect().width;
    let scale = "";
    let left = 10;

    if (width > 154) {
      const s = 154 / width;
      scale = `scaleX(${s})`;
    } else {
      left = left + (154 - width) / 2;
    }

    const t = `transform: translate(${left}px,25mm) ${scale};`;

    badge.setAttribute("style", t);
  });
});
