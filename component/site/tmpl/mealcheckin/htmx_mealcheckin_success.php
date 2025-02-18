<h1>Success</h1>

<?php

#var_dump([$this->msg, $this->error, $this->dinner]);

$style = match (strtolower($this->dinner)) {
  'beef' => 'meal-beef',
  'chicken' => 'meal-chicken',
  'fish' => 'meal-fish',
  'vegetarian' => 'meal-vegan',
  default => ''
};

?>

<div class="d-grid gap-2">
  <button class="btn btn-lg <?= $style ?>" type="button"><?= $this->msg ?></button>
</div>
