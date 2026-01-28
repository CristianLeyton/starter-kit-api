<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    #[Computed]
    public function title()
    {
        return 'Welcome';
    }
};
?>

<div class="text-5xl text-center mt-10 text-red-500 font-black">
    Welcome world
</div>
