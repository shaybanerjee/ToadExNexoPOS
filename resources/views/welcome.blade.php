@extends( 'layout.base' )

@section( 'layout.base.body' )
    <div id="page-container" class="h-full w-full flex" style="background-color:#002b5b">
        <div class="container flex-auto flex-col items-center justify-center flex m-4 sm:mx-auto">
            <div class="flex justify-center items-center py-6">
                <img src="{{ asset( 'svg/toadex_log.jpg' ) }}" style="width:400px;" alt="ToadEx">
            </div>
            <div class="ns-box rounded shadow w-full md:w-1/2 lg:w-1/3 overflow-hidden">
                <div id="section-header" class="ns-box-header p-4">
                    <h1 class="text-center b-8" style="font-size:20px;">Welcome to the ToadEx POS</h1>
                    <br>
                    <p class="text-center b-8 text-sm">This demo showcases the core features of ToadEx POS.</p>
                </div>
                <div class="ns-box-footer flex shadow border-t">
                    <div class="flex w-1/2"><a class="link text-sm w-full py-2 text-center" href="{{ ns()->route( 'ns.dashboard.home' ) }}">{{ __( 'Dashboard' ) }}</a></div>
                    <div class="flex w-1/2"><a class="link text-sm w-full py-2 text-center" href="{{ ns()->route( 'ns.login' ) }}">{{ __( 'Sign In' ) }}</a></div>
                </div>
            </div>
        </div>
    </div>
@endsection