# Transition styling for carousel

Per [Bootstrap v5.2 Carousel Documentation](https://getbootstrap.com/docs/5.2/components/carousel/#custom-transition):

Example

```
.carousel-item {
  transition: transform 2s ease, opacity 1s ease;
}
```

Add to your favorite css file.

# Accordion styling

```
.accordion-button:not(.collapsed) {
  color           : #fff;
  background-color: #dc3545;
}
.accordion-item {
  background-color: inherit;
  border          : 2px solid #ffae00;
  color: inherit
}
.accordion-button {
  background-color: #dc354641;
}
/* Down arrow */
.accordion-button.collapsed::after {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23fff'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
}

```

Probably other classes to adjust, but that was the baseline I needed. Adjust as you like and add to your favorite css file.