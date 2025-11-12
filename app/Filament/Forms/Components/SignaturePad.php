<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;

class SignaturePad extends Field
{
    protected string $view = 'filament.forms.components.signature-pad';

    protected int|Closure|null $height = 200;

    protected string|Closure|null $backgroundColor = null;

    protected string|Closure|null $penColor = null;

    protected string|Closure|null $clearButtonLabel = null;

    protected bool|Closure $showClearButton = true;

    public function height(int|Closure|null $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->evaluate($this->height);
    }

    public function backgroundColor(string|Closure|null $color): static
    {
        $this->backgroundColor = $color;

        return $this;
    }

    public function getBackgroundColor(): ?string
    {
        return $this->evaluate($this->backgroundColor);
    }

    public function penColor(string|Closure|null $color): static
    {
        $this->penColor = $color;

        return $this;
    }

    public function getPenColor(): ?string
    {
        return $this->evaluate($this->penColor);
    }

    public function clearButtonLabel(string|Closure|null $label): static
    {
        $this->clearButtonLabel = $label;

        return $this;
    }

    public function getClearButtonLabel(): string
    {
        return $this->evaluate($this->clearButtonLabel) ?? __('Clear signature');
    }

    public function showClearButton(bool|Closure $condition = true): static
    {
        $this->showClearButton = $condition;

        return $this;
    }

    public function getShowClearButton(): bool
    {
        return (bool) $this->evaluate($this->showClearButton);
    }
}
