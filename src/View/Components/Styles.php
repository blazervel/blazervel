<?php

namespace Blazervel\Blazervel\View\Components;

use Blazervel\Blazervel\Component;

class Styles extends Component
{
  protected string $href;

  public function __construct(string $href)
  {
    $this->href = $href;
  }

  public function render()
  {
    return (
      "<link href=\"{$this->href}\" rel=\"stylesheet\"/>"
    );
  }
}