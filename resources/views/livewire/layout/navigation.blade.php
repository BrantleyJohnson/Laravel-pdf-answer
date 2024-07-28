<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; 

function getSections() {
    $sections = \App\Models\Section::with(['pdfs' => function($query) {  $query->where('role', 'primary'); }])->get()->toArray();
    return $sections;
}

function getInitials() {
    $words = explode(" ", auth()->user()->name);
        $initials = "";

        foreach ($words as $w) {
            $initials .= mb_substr($w, 0, 1);
        }
        return $initials;
}
?>

<?php
function isShared() {
    $is_shared = false;
    $uri_seg = explode("/", ltrim($_SERVER['REQUEST_URI'], "/"));
    if($uri_seg[0] == "sharable") {
        $is_shared = true;
    }
    return $is_shared;
}
?>

<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16" style="justify-content:space-between;">
            <div class="flex">
                <!-- Logo -->
                <!-- <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" wire:navigate>
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div> -->

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                        <p class="underline text-center text-sm">{{ isShared() ? "Home" : "References" }}</p>
                    </x-nav-link>
                </div>
            </div>
                
    
            @if(Auth::check())
            
                <div style="z-index: 100;  margin-top: 20px;">
                    <div class="">
                        
                        @if (!isShared())
                            <button @click="showSectionMenu = ! showSectionMenu"  class="font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center" type="button">
                                Select Code&nbsp;&nbsp;
                                <i x-show="!showSectionMenu" class="fa-2xs fa-solid fa-chevron-right"></i>
                                <i x-show="showSectionMenu" x-cloak class="fa-2xs fa-solid fa-chevron-down"></i>
                            </button>
                        @endif
                        <!-- Dropdown menu -->
                        <div @click.outside="showSectionMenu = false" x-show="showSectionMenu" x-cloak x-transition class="z-10 bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700" style="position: fixed;">
                            @foreach (getSections() as $sd)
                                <div class="px-2 py-2 text-sm text-gray-700 dark:text-gray-200" x-data="{ selected_{{$sd['id']}}: false }">
                                    <div>
                                        <span  @click="selected_{{$sd['id']}} = ! selected_{{$sd['id']}}"  class="block px-2 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-white">
                                            <!-- <input :checked="selected_{{$sd['id']}} ? 'true' : ''" type="checkbox" style="transform: scale(0.75);"> --> {{ $sd['name'] }}
                                        </span>
                                    </div>
                                
                                    @foreach ($sd['pdfs'] as $pdf)
                                        <div class="px-2 text-sm text-gray-700 dark:text-gray-200" >
                                            <div>
                                                <span class="block px-6 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-white">
                                                    <input  type="checkbox" value="{{$pdf['id']}}" :checked="selected_{{$sd['id']}} ? 'true' : ''"  style="transform: scale(0.75);" x-model="selectedPdf" \> {{ $pdf['name'] }} 
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>          
                            @endforeach
                        <!-- <center>
                                <button @click="setSelected(selectedPdf)" class="text-white font-bold py-2 px-6 rounded  dark:text-white" style="margin-bottom: 5px; margin-top: 15px; background-color: rgb(8 145 178);">
                                    Apply
                                </button>
                            </center> -->
                        </div>
                    </div>
                </div>
        
                
                    
                <!-- Settings Dropdown -->
                <div class="hidden sm:flex sm:items-center sm:ms-6">
                    @if (!isShared())
                        <button @click="startNewChat()" class="new-chat-btn inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <i class="fa-xs fa-solid fa-pen "></i>  
                        </button>
                    @endif


                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                <div x-data="{{ json_encode(['name' => getInitials() ]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile')" wire:navigate>
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <button wire:click="logout" class="w-full text-start">
                                <x-dropdown-link>
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </button>
                        </x-slot>
                    </x-dropdown>
                    @if (!isShared())
                    <button @click="showHistory = ! showHistory" class="new-chat-btn inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                        <i class="fa-xs fa-solid fa-clock-rotate-left "></i>  
                    </button>
                    @endif
                </div>

                <!-- Hamburger -->
                <div class="-me-2 flex items-center max-sm:hidden mobile-only">
                    <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            @endif
        </div>
        
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        @if(Auth::check())
        <!-- Responsive Settings Options -->
            <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800 dark:text-gray-200" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                    <div class="font-medium text-sm text-gray-500">{{ auth()->user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile')" wire:navigate>
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <button wire:click="logout" class="w-full text-start">
                        <x-responsive-nav-link>
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </button>
                </div>
            </div>
        @endif
    </div>
</nav>
