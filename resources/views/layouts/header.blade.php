<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{env('APP_NAME', 'Netsuite Printing')}}</title>
    <link rel="stylesheet" href="{{ asset('/body_css/vendors/feather/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('/body_css/vendors/ti-icons/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('/body_css/vendors/css/vendor.bundle.base.css') }}">
    @yield('css_header')
    <link rel="stylesheet" href="{{ asset('/body_css/vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet" href="{{ asset('/body_css/vendors/ti-icons/css/themify-icons.css') }}">

    <link rel="stylesheet" href="{{ asset('/body_css/vendors/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/body_css/vendors/select2-bootstrap-theme/select2-bootstrap.min.css') }}">

    <link rel="stylesheet" href="{{ asset('/body_css/css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="{{ asset('/vendor/sweetalert2/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">

</head>

<style>
    .loader {
        position: fixed;
        left: 0px;
        top: 0px;
        width: 100%;
        height: 100%;
        z-index: 9999;
        background: url("{{ asset('images/WBGC.png')}}") 50% 50% no-repeat white ;
        opacity: .8;
        background-size:10vh 5vh;
    }
    
    .card-header-radius {
        border-top-right-radius:20px !important; 
        border-top-left-radius: 20px !important;
    }

    .preloader {
		background-color: #f7f7f7;
		width: 100%;
		height: 100%;
		position: fixed;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		z-index: 999999;
		-webkit-transition: .6s;
		-o-transition: .6s;
		transition: .6s;
		margin: 0 auto;
        /* opacity: .8; */
	}

	.preloader .preloader-circle {
		width: 100px;
		height: 100px;
		position: relative;
		border-style: solid;
		border-width: 1px;
		border-top-color:$theme-color;
		border-bottom-color: transparent;
		border-left-color: transparent;
		border-right-color: transparent;
		z-index: 10;
		border-radius: 50%;
		-webkit-box-shadow: 0 1px 5px 0 rgba(35, 181, 185, 0.15);
		box-shadow: 0 1px 5px 0 rgba(35, 181, 185, 0.15);
		background-color: #ffffff;
		-webkit-animation: zoom 2000ms infinite ease;
		animation: zoom 2000ms infinite ease;
		-webkit-transition: .6s;
		-o-transition: .6s;
		transition: .6s;
	}
	.preloader .preloader-circle2 {
		border-top-color: #0078ff;
	}
	.preloader .preloader-img {
		position: absolute;
		top: 50%;
		z-index: 200;
		left: 0;
		right: 0;
		margin: 0 auto;
		text-align: center;
		display: inline-block;
		-webkit-transform: translateY(-50%);
		-ms-transform: translateY(-50%);
		transform: translateY(-50%);
		padding-top: 6px;
		-webkit-transition: .6s;
		-o-transition: .6s;
		transition: .6s;
	}

	.preloader .preloader-img img {
		max-width: 55px;
	}
	.preloader .pere-text strong{
		font-weight: 800;
		color:#dca73a ;
		text-transform: uppercase;
	}

    @-webkit-keyframes zoom {
		0% {
			-webkit-transform: rotate(0deg);
			transform: rotate(0deg);
			-webkit-transition: .6s;
			-o-transition: .6s;
			transition: .6s;
		}

		100% {
			-webkit-transform: rotate(360deg);
			transform: rotate(360deg);
			-webkit-transition: .6s;
			-o-transition: .6s;
			transition: .6s;
		}
	}

    .pagination {
        margin-top: 15px;
    }
</style>

<body>
    <!-- Preloader Start -->
    <div id="preloader-active" style="display: none;">
        <div class="preloader d-flex align-items-center justify-content-center">
            <div class="preloader-inner position-relative">
                <div class="preloader-circle"></div>
                <div class="preloader-img pere-text">
                    <img src="{{asset('images/WBGC.png')}}" alt="">
                </div>
            </div>
        </div>
    </div>
    <!-- Preloader Start -->

    <div class="container-scroller">
        <!-- partial:partials/_navbar.html -->
        <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
            <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
                <a class="navbar-brand brand-logo" href="{{url('netsuite/search')}}"><img src="{{asset('images/WBGC.png')}}" class="mr-2" alt="logo" style="height: 5vh;"/></a>
                {{-- <a class="navbar-brand brand-logo-mini" href="index.html"><img src="{{asset('images/WBGC.png')}}" alt="logo" /></a> --}}
            </div>
            <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
                <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
                    <span class="icon-menu"></span>
                </button>
                {{-- <ul class="navbar-nav navbar-nav-right">
                    <li class="nav-item nav-profile dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="profileDropdown">
                            <img src="img/user.png" alt="profile" />
                        </a>

                        <div class="dropdown-menu dropdown-menu-right navbar-dropdown"
                            aria-labelledby="profileDropdown">
                            <a class="dropdown-item">
                                <i class="ti-settings text-primary"></i>
                                Settings
                            </a>
                            <a class="dropdown-item" href="{{ route('logout') }}" onclick="logout(); show();">
                                <i class="ti-power-off text-primary"></i>
                                {{ __('Logout') }}
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </li>
                </ul> --}}
            </div>
        </nav>
        <!-- partial -->
        <div class="container-fluid page-body-wrapper">
            <nav class="sidebar sidebar-offcanvas" id="sidebar">
                <ul class="nav">
                    {{-- <li class="nav-item" onclick="show()">
                        <a class="nav-link" href="{{ url('/home') }}">
                            <i class="icon-grid menu-icon"></i>
                            <span class="menu-title">Dashboard</span>
                        </a>
                    </li> --}}
                    {{-- <li class="nav-item" onclick="show()">
                        <a class="nav-link" href="{{ url('/products') }}">
                            <i class="ti-package menu-icon"></i>
                            <span class="menu-title">Product</span>
                        </a>
                    </li>
                    <li class="nav-item" onclick="show()">
                        <a class="nav-link" href="{{ url('/raw_materials') }}">
                            <i class="ti-archive menu-icon"></i>
                            <span class="menu-title">Raw Materials</span>
                        </a>
                    </li> --}}
                    {{-- <li class="nav-item" onclick="show()"> 
                        <a class="nav-link" href="{{ url('/available') }}">
                            <i class="icon-check menu-icon"></i>
                            <span class="menu-title">Available</span>
                        </a>
                    </li> --}}
                    {{-- <li class="nav-item" onclick="show()"> 
                        <a class="nav-link" href="{{ url('/shipments') }}">
                            <i class="ti-import menu-icon"></i>
                            <span class="menu-title">Inbound</span>
                        </a>
                    </li> --}}
                    {{-- <li class="nav-item" onclick="show()"> 
                        <a class="nav-link" href="{{ url('/outbound') }}">
                            <i class="ti-export menu-icon"></i>
                            <span class="menu-title">Outbound</span>
                        </a>
                    </li> --}}
                    <li class="nav-item" onclick="show()"> 
                        <a class="nav-link" href="{{ url('/netsuite/search') }}">
                            <i class="ti-bookmark menu-icon"></i>
                            <span class="menu-title">AP Voucher</span>
                        </a>
                    </li>
                    {{-- <li class="nav-item">
                        <a class="nav-link" data-toggle="collapse" href="#setup" aria-expanded="false"
                            aria-controls="setup">
                            <i class="icon-layout menu-icon"></i>
                            <span class="menu-title">Setup</span>
                            <i class="menu-arrow"></i>
                        </a>
                        <div class="collapse" id="setup">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item" onclick="show()"> <a class="nav-link" href="{{ url('/users') }}">User</a></li>
                                <li class="nav-item" onclick="show()"> <a class="nav-link" href="{{ url('/group_setup') }}">Ingredient Group</a></li>
                                <li class="nav-item" onclick="show()"> <a class="nav-link" href="{{ url('/raw_material') }}">Other Raw Materials</a></li>
                            </ul>
                        </div>
                    </li> --}}
                </ul>
            </nav>
            <!-- partial -->
            <div class="main-panel">
                <div class="loader" style="display: none;" id="loader"></div>
                <div class="content-wrapper" style="background: #F5F7FF;">
                    @yield('content')
                </div>
                <!-- content-wrapper ends -->
                <!-- partial:partials/_footer.html -->
                {{-- <footer class="footer">
                    <div class="d-sm-flex justify-content-center justify-content-sm-between">
                        <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright © 2021.
                            Premium <a href="https://www.bootstrapdash.com/" target="_blank">Bootstrap admin
                                template</a> from BootstrapDash. All rights reserved.</span>
                        <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center">Hand-crafted & made
                            with <i class="ti-heart text-danger ml-1"></i></span>
                    </div>
                    <div class="d-sm-flex justify-content-center justify-content-sm-between">
                        <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Distributed by <a
                                href="https://www.themewagon.com/" target="_blank">Themewagon</a></span>
                    </div>
                </footer> --}}
                <!-- partial -->
            </div>
            <!-- main-panel ends -->
        </div>
        <!-- page-body-wrapper ends -->
    </div>
    
    <!-- container-scroller -->
    @include('sweetalert::alert')
    <!-- plugins:js -->
    <script src="{{asset('body_css/js/jquery-3.6.0.min.js')}}"></script>
    <script src="{{ asset('/body_css/vendors/chart.js/Chart.min.js') }}"></script>
    <script src="{{ asset('/body_css/vendors/js/vendor.bundle.base.js') }}"></script>

    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="{{ asset('/body_css/vendors/select2/select2.min.js') }}"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->

    <!-- endinject -->
    <!-- Custom js for this page-->
    <script src="{{ asset('/body_css/js/dashboard.js') }}"></script>
    <script src="{{ asset('body_css/js/select2.js') }}"></script>


    <script src="{{ asset('/body_css/vendors/datatables.net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('/body_css/vendors/datatables.net-bs4/dataTables.bootstrap4.js') }}"></script>
    {{-- <script src="{{ asset('/body_css/vendors/jquery.repeater/jquery.repeater.min.js') }}"></script> --}}

    <script src="{{ asset('/body_css/js/dataTables.select.min.js') }}"></script>

    <script src="{{ asset('/body_css/js/off-canvas.js') }}"></script>
    <script src="{{ asset('/body_css/js/hoverable-collapse.js') }}"></script>
    <script src="{{ asset('/body_css/js/template.js') }}"></script>
    {{-- <script src="{{ asset('/body_css/js/settings.js') }}"></script> --}}
    {{-- <script src="{{ asset('/body_css/js/todolist.js') }}"></script> --}}

    <script src="{{ asset('/body_css/js/tabs.js') }}"></script>
    {{-- <script src="{{ asset('/body_css/js/form-repeater.js') }}"></script> --}}
    {{-- <script src="{{ asset('/body_css/vendors/sweetalert/sweetalert.min.js') }}"></script> --}}

    <script src="{{ asset('/body_css/vendors/inputmask/jquery.inputmask.bundle.js') }}"></script>
    <script src="{{ asset('/body_css/vendors/inputmask/jquery.inputmask.bundle.js') }}"></script>
    <script src="{{ asset('/body_css/js/inputmask.js') }}"></script>
    <script src="{{ asset('/vendor/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>

    <script>
        function show() {
            // document.getElementById("loader").style.display="block";
            document.getElementById("preloader-active").style.display="block";
        }
        function logout() {
            event.preventDefault();
            document.getElementById('logout-form').submit();
        }

        $(document).ready(function() {
            $('.tablewithSearch').DataTable({
                processing: false,
                serverSide: false,
                ordering: false,
                dom: 'Bfrtip',
                paginate: 10,
            });
        });
    </script>

    @yield('js')
</body>

</html>