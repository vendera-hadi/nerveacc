<?php
  // hardcode sementara
  $access = Auth::user()->name;
?>
<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <!-- Sidebar user panel (optional) -->
        @if (! Auth::guest())
            <div class="user-panel">
                <div class="pull-left image">
                    <img src="{{asset('/img/user2-160x160.jpg')}}" class="img-circle" alt="User Image" />
                </div>
                <div class="pull-left info">
                    <p>{{ Auth::user()->name }}</p>
                    <!-- Status -->
                    <a href="#"><i class="fa fa-circle text-success"></i> {{ trans('adminlte_lang::message.online') }}</a>
                </div>
            </div>
        @endif

        <!-- search form (Optional) -->
        <!-- <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="{{ trans('adminlte_lang::message.search') }}..."/>
              <span class="input-group-btn">
                <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i></button>
              </span>
            </div>
        </form> -->
        <!-- /.search form -->

        <!-- Sidebar Menu -->
        <ul class="sidebar-menu">
            <?php
              $masterUrls = [url('coa'), url('department'), url('invtype'), url('groupaccount'), url('company'), url('config'), url('roles'), url('users')];
              $masters = [1,5,6,7,11];
              $companySetup = [1,5,6];
              $otorisasi = [7,11];
            ?>
            @if(Session::get('role')==1 || !empty(array_intersect($masters, Session::get('permissions'))) )
            <li class="treeview @if(in_array(Request::url(),$masterUrls)){{'active'}}@endif">
              <a href="#">
                <i class="fa fa-gears"></i> <span>MASTER DATA</span>
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul class="treeview-menu">      
                @if(Session::get('role')==1 || !empty(array_intersect($companySetup, Session::get('permissions'))) )
                <li>
                    <a href="#"><i class="fa fa-circle"></i> Company Setup
                      <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                      </span>
                    </a>
                    <ul class="treeview-menu @if(in_array(Request::url(),$masterUrls)){{'active menu-open'}}@endif" @if(in_array(Request::url(),$masterUrls)) style="display:block" @endif>
                      @if(Session::get('role')==1 || in_array(1,Session::get('permissions')))
                      <li @if(Request::url() == url('department')) class="active" @endif><a href="{{url('department')}}"><i class="fa fa-circle-o"></i> Department</a></li>
                      @endif
                      @if(Session::get('role')==1 || in_array(5,Session::get('permissions')))
                      <li @if(Request::url() == url('company')) class="active" @endif><a href="{{url('company')}}"><i class="fa fa-circle-o"></i> Company Details</a></li>
                      @endif
                      @if(Session::get('role')==1 || in_array(6,Session::get('permissions')))
                      <li @if(Request::url() == url('config')) class="active" @endif><a href="{{url('config')}}"><i class="fa fa-circle-o"></i> Other Config</a></li>
                      @endif
                    </ul>
                </li>
                @endif

                @if(Session::get('role')==1)
                <li>
                    <a href="#"><i class="fa fa-circle"></i> Otorisasi
                      <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                      </span>
                    </a>
                    <ul class="treeview-menu @if(in_array(Request::url(),$masterUrls)){{'active menu-open'}}@endif" @if(in_array(Request::url(),$masterUrls)) style="display:block" @endif>               
                        @if(Session::get('role')==1)
                        <li @if(Request::url() == url('roles')) class="active" @endif><a href="{{url('roles')}}"><i class="fa fa-circle-o"></i> Roles</a></li>
                        @endif
                        @if(Session::get('role')==1)
                        <li @if(Request::url() == url('users')) class="active" @endif><a href="{{url('users')}}"><i class="fa fa-circle-o"></i> Users List</a></li>
                        @endif
                    </ul>
                </li>
                @endif
                
                @if(Session::get('role')==1 || in_array(7,Session::get('permissions')))
                <li @if(Request::url() == url('invtype')) class="active" @endif><a href="{{url('invtype')}}"><i class="fa fa-circle-o"></i> Invoice Type</a></li>
                @endif
                @if(Session::get('role')==1 || in_array(11,Session::get('permissions')))
                <li @if(Request::url() == url('coa')) class="active" @endif><a href="{{url('coa')}}"><i class="fa fa-circle-o"></i> Chart of Account</a></li>
                @endif

                                   
                <!-- <li @if(Request::url() == url('groupaccount')) class="active" @endif><a href="{{url('groupaccount')}}"><i class="fa fa-circle-o"></i> Group Account</a></li> -->
                
                <!-- <li><a href="{{url('groupaccdetail')}}"><i class="fa fa-circle-o"></i> Group Account Detail</a></li> -->
                <!-- <li><a href="{{url('rentalperiod')}}"><i class="fa fa-circle-o"></i> Rental Period</a></li> -->
                <!-- <li><a href="{{url('supplier')}}"><i class="fa fa-circle-o"></i> Supplier</a></li> -->
              </ul>
            </li>
            @endif

            <?php
              $unitUrls = [url('unit'), url('unittype'), route('floor.index')];

              $units = [15,19,23];
              $tenancys = [15,19,23,27,31,51];
            ?>
            @if(Session::get('role')==1 || !empty(array_intersect($tenancys, Session::get('permissions'))) ) 
            <li class="treeview @if(in_array(Request::url(),$unitUrls) || Request::url() == url('tenant') || Request::url() == url('typetenant')
            || Request::url() == route('marketing.index') || Request::url() == route('report.tenancyview') ||  Request::url() == url('vaccount')){{'active'}}@endif">
              <a href="#">
                <i class="fa fa-building-o"></i> <span>TENANCY</span>
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul class="treeview-menu @if(in_array(Request::url(),$unitUrls)){{'active'}}@endif">
                @if(Session::get('role')==1 || !empty(array_intersect($units, Session::get('permissions'))) ) 
                <li>
                    <a href="#"><i class="fa fa-circle"></i> Units
                      <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                      </span>
                    </a>
                    <ul class="treeview-menu @if(in_array(Request::url(),$unitUrls)){{'active menu-open'}}@endif" @if(in_array(Request::url(),$unitUrls)) style="display:block" @endif>
                      @if(Session::get('role')==1 || in_array(15,Session::get('permissions')))
                      <li @if(Request::url() == url('unit')) class="active" @endif><a href="{{ url('unit') }}"><i class="fa fa-circle-o"></i> Unit</a></li>
                      @endif
                      @if(Session::get('role')==1 || in_array(19,Session::get('permissions')))
                      <li @if(Request::url() == url('unittype')) class="active" @endif><a href="{{ url('unittype') }}"><i class="fa fa-circle-o"></i> Unit Type</a></li>
                      @endif
                      @if(Session::get('role')==1 || in_array(23,Session::get('permissions')))
                      <li @if(Request::url() == route('floor.index')) class="active" @endif><a href="{{route('floor.index')}}"><i class="fa fa-circle-o"></i> Floor</a></li>
                      @endif
                    </ul>
                </li>
                @endif

                @if(Session::get('role')==1 || in_array(27,Session::get('permissions')))
                <li @if(Request::url() == url('tenant')) class="active" @endif><a href="{{ url('tenant') }}"><i class="fa fa-circle-o"></i> Tenant/Owner</a></li>
                @endif
                @if(Session::get('role')==1 || in_array(31,Session::get('permissions')))
                <li @if(Request::url() == url('typetenant')) class="active" @endif><a href="{{url('typetenant')}}"><i class="fa fa-circle-o"></i> Tenant/Owner Type</a></li>
                @endif

                @if(Session::get('role')==1 || in_array(51,Session::get('permissions')))
                <li @if(Request::url() == route('report.tenancyview')) class="active" @endif><a href="{{route('report.tenancyview')}}"><i class="fa fa-circle-o"></i> Reports</a></li>
                @endif
              </ul>
            </li>
            @endif

            
            <?php
              $arUrls = [route('invoice.generate'), route('invoice.index'), route('aging.index'), route('report.arview'), route('journal.index'), route('cash_bank.index'), route('payment.index'),url('period_meter'), route('genledger.index'),route('trentry.index'),route('clentry.index'),route('report.glview')];
              $tenancyUrls = [route('contract.index'), route('contract.confirmation'), route('contract.addendum'), route('contract.renewal'), route('contract.termination'), route('contract.unclosed')];
              
              $accreceivables = [52,58,59,63,68,76,35,39,40,41,42,43,44];
              $billingInfos = [35,39,40,41,42,43];
              $generalledger = [64];
              $cashbanks = [72];
            ?>
            <li class="treeview @if(in_array(Request::url(),$tenancyUrls) || in_array(Request::url(),$arUrls) || Request::url() == url('cost_item')){{'active'}}@endif">
              <a href="#">
                <i class="fa fa-book"></i> <span>FINANCE</span>
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul class="treeview-menu">
                @if(Session::get('role')==1 || !empty(array_intersect($accreceivables, Session::get('permissions'))) ) 
                <li>
                    <a href="#"><i class="fa fa-circle"></i> Account Receivables
                        <span class="pull-right-container">
                          <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu @if(in_array(Request::url(),$tenancyUrls) || in_array(Request::url(),$arUrls) || Request::url() == url('cost_item')){{'active menu-open'}}@endif" @if(in_array(Request::url(),$tenancyUrls) || in_array(Request::url(),$arUrls) || Request::url() == url('cost_item')) style="display:block" @endif>
                      @if(Session::get('role')==1 || in_array(52,Session::get('permissions')))
                      <li @if(Request::url() == url('period_meter')) class="active" @endif><a href="{{url('period_meter')}}"><i class="fa fa-circle-o"></i> Meter Input</a></li>
                      @endif
                      @if(Session::get('role')==1 || in_array(58,Session::get('permissions')))
                      <li @if(Request::url() == route('invoice.generate')) class="active" @endif><a href="{{route('invoice.generate')}}"><i class="fa fa-circle-o"></i> Generate Invoice</a></li>
                      @endif
                      @if(Session::get('role')==1 || in_array(59,Session::get('permissions')))
                      <li @if(Request::url() == route('invoice.index')) class="active" @endif><a href="{{route('invoice.index')}}"><i class="fa fa-circle-o"></i> Invoices</a></li>
                      @endif
                      @if(Session::get('role')==1 || in_array(63,Session::get('permissions')))
                      <li @if(Request::url() == route('aging.index')) class="active" @endif><a href="{{route('aging.index')}}"><i class="fa fa-circle-o"></i> Aging Invoices</a></li>
                      @endif
                      
                      @if(Session::get('role')==1 || in_array(68,Session::get('permissions')))
                      <li @if(Request::url() == route('payment.index')) class="active" @endif><a href="{{route('payment.index')}}"><i class="fa fa-circle-o"></i> Payment Invoice</a></li>
                      @endif
                      
                      @if(Session::get('role')==1 || in_array(76,Session::get('permissions')))
                      <li @if(Request::url() == route('report.arview')) class="active" @endif><a href="{{route('report.arview')}}"><i class="fa fa-circle-o"></i> Reports</a></li>
                      @endif

                      @if(Session::get('role')==1 || in_array(44,Session::get('permissions')))
                      <li @if(Request::url() == url('cost_item')) class="active" @endif><a href="{{url('cost_item')}}"><i class="fa fa-circle-o"></i> Component Billing</a></li>       
                      @endif

                      @if(Session::get('role')==1 || !empty(array_intersect($billingInfos, Session::get('permissions'))) )
                      <li>
                        <a href="#"><i class="fa fa-circle"></i> Billing Information
                          <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                          </span>
                        </a>
                        <ul class="treeview-menu @if(in_array(Request::url(),$tenancyUrls)){{'active menu-open'}}@endif" @if(in_array(Request::url(),$tenancyUrls)) style="display:block" @endif>
                          @if(Session::get('role')==1 || in_array(35,Session::get('permissions')))
                          <li @if(Request::url() == route('contract.index')) class="active" @endif><a href="{{route('contract.index')}}"><i class="fa fa-circle-o"></i> Billing Info</a></li>
                          @endif
                          @if(Session::get('role')==1 || in_array(39,Session::get('permissions')))
                          <li @if(Request::url() == route('contract.confirmation')) class="active" @endif><a href="{{route('contract.confirmation')}}"><i class="fa fa-circle-o"></i> Billing Info Confirmation</a></li>
                          @endif
                          @if(Session::get('role')==1 || in_array(40,Session::get('permissions')))
                          <li @if(Request::url() == route('contract.addendum')) class="active" @endif><a href="{{route('contract.addendum')}}"><i class="fa fa-circle-o"></i> Billing Info Addendum</a></li>
                          @endif
                          @if(Session::get('role')==1 || in_array(41,Session::get('permissions')))
                          <li @if(Request::url() == route('contract.renewal')) class="active" @endif><a href="{{route('contract.renewal')}}"><i class="fa fa-circle-o"></i> Billing Info Renewal</a></li>
                          @endif
                          @if(Session::get('role')==1 || in_array(42,Session::get('permissions')))
                          <li @if(Request::url() == route('contract.termination')) class="active" @endif><a href="{{route('contract.termination')}}"><i class="fa fa-circle-o"></i> Billing Info Termination</a></li>
                          @endif
                          @if(Session::get('role')==1 || in_array(43,Session::get('permissions')))
                          <li @if(Request::url() == route('contract.unclosed')) class="active" @endif><a href="{{route('contract.unclosed')}}"><i class="fa fa-circle-o"></i> Reminder Billing <span class="pull-right-container">
                          @endif
                    <span class="label label-primary pull-right">{{$notif_unclosed}}</span>
                  </span></a></li>
                        </ul>
                      </li>
                      @endif

                    </ul>
                </li>
                @endif

                @if(Session::get('role')==1 || !empty(array_intersect($generalledger, Session::get('permissions'))) ) 
                <li>
                    <a href="#"><i class="fa fa-circle"></i> General Ledger (GL)
                        <span class="pull-right-container">
                          <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu @if(in_array(Request::url(),$arUrls)){{'active menu-open'}}@endif" @if(in_array(Request::url(),$arUrls)) style="display:block" @endif>
                      <!-- if(Session::get('role')==1 || in_array(64,Session::get('permissions')))
                      <li if(Request::url() == route('journal.index')) class="active" endif><a href="route('journal.index')"><i class="fa fa-circle-o"></i> Journal Entries</a></li>
                      endif -->
                      @if(Session::get('role')==1 || in_array(77,Session::get('permissions')))
                      <li @if(Request::url() == route('genledger.index')) class="active" @endif><a href="{{route('genledger.index')}}"><i class="fa fa-circle-o"></i> General Ledger</a></li>
                      @endif
                      @if(Session::get('role')==1 || in_array(65,Session::get('permissions')))
                      <li @if(Request::url() == route('trentry.index')) class="active" @endif><a href="{{route('trentry.index')}}"><i class="fa fa-circle-o"></i> Transaction Entry</a></li>
                      @endif
                      @if(Session::get('role')==1 || in_array(78,Session::get('permissions')))
                      <li @if(Request::url() == route('clentry.index')) class="active" @endif><a href="{{route('clentry.index')}}"><i class="fa fa-circle-o"></i> Close Entry</a></li>
                      @endif
                       @if(Session::get('role')==1 || in_array(77,Session::get('permissions')))
                      <li @if(Request::url() == route('report.glview')) class="active" @endif><a href="{{route('report.glview')}}"><i class="fa fa-circle-o"></i> GL Report</a></li>
                      @endif
                      <li><a href="#"><i class="fa fa-circle-o"></i> Profit and Loss</a></li>
                      <li><a href="#"><i class="fa fa-circle-o"></i> Trial Balance</a></li>
                      <li><a href="#"><i class="fa fa-circle-o"></i> Balance Sheet</a></li>
                      <li><a href="#"><i class="fa fa-circle-o"></i> Budget</a></li>
                    </ul>
                </li>
                @endif

                @if(Session::get('role')==1 || !empty(array_intersect($cashbanks, Session::get('permissions'))) )
                  <li>
                    <a href="#"><i class="fa fa-circle"></i> Bank Book
                        <span class="pull-right-container">
                          <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu @if(in_array(Request::url(),$arUrls)){{'active menu-open'}}@endif" @if(in_array(Request::url(),$arUrls)) style="display:block" @endif>
                      @if(Session::get('role')==1 || in_array(72,Session::get('permissions')))
                      <li @if(Request::url() == route('cash_bank.index')) class="active" @endif><a href="{{route('cash_bank.index')}}"><i class="fa fa-circle-o"></i> Cash Bank List</a></li>
                      @endif
                      <li ><a href="#"><i class="fa fa-circle-o"></i> Reconcile Bank</a></li>
                    </ul>
                  </li>
                @endif

                <li ><a href="#"><i class="fa fa-circle-o"></i> Account Payable</a></li>
                

              </ul>
            </li>
           

        </ul><!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
</aside>
