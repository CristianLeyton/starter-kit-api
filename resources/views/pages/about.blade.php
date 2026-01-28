<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

new #[Layout('layouts.app')] class extends Component
{
    #[Computed]
    public function title()
    {
        return 'About';
    }
};
?>

<div>
    <h1 class="text-5xl text-center mt-10 text-red-500 font-black">
        About world
    </h1>
</div>