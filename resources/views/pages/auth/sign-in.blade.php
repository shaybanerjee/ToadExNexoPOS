<?php

use App\Classes\Hook;
use App\Classes\Output;

$beforeForm     =   new Output;
$afterForm      =   new Output;

Hook::action( 'ns.before-login-fields', $beforeForm );
Hook::action( 'ns.after-login-fields', $afterForm );
?>
@extends( 'layout.base' )

@section( 'layout.base.body' )
    <div id="page-container" class="h-full w-full flex items-center overflow-y-auto pb-10" style="background-color:#002b5b">
        <div class="container mx-auto p-4 md:p-0 flex-auto items-center justify-center flex">
            <div id="sign-in-box" class="w-full md:w-3/5 lg:w-2/5 xl:w-84">
                <div class="flex justify-center items-center py-6">
                    @if ( ! ns()->option->get( 'ns_store_square_logo', false ) )
                    <img src="{{ asset( 'svg/toadex_log.jpg' ) }}" alt="ToadEx POS">
                    @else
                    <img src="{{ ns()->option->get( 'ns_store_square_logo' ) }}" alt="ToadEx POS">
                    @endif
                </div>
                <x-session-message></x-session-message>
                {!! $beforeForm !!}
                @include( '/common/auth/sign-in-form' )
                {!! $afterForm !!}
            </div>
        </div>
    </div>
@endsection

@section( 'layout.base.footer' )
    @parent
    {!! Hook::filter( 'ns-login-footer', new Output ) !!}
    @vite([ 'resources/ts/auth.ts' ])
@endsection