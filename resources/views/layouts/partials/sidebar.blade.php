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
                    <img src="{{asset('/upload/'.Auth::user()->image)}}" class="img-circle" alt="User Image" />
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
            <li @if(Request::url() == url('home')){{'class=active'}}@endif>
              <a href="{{url('home')}}"><i class="fa fa-home"></i><span>DASHBOARD</span></a>
            </li>
            <?php
              $masterUrls = [url('invtype'), url('groupaccount'), url('company'), url('config')];
              $otoUrls = [url('roles'), url('users')];
              $masters = [5,6,7];
              $companySetup = [5,6];
              $otorisasi = [7];
            ?>
            @if(Session::get('role')==1 || !empty(array_intersect($masters, Session::get('permissions'))) )
            <li class="treeview @if(in_array(Request::url(),$masterUrls) || in_array(Request::url(),$otoUrls)){{'active'}}@endif">
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
                    <ul class="treeview-menu @if(in_array(Request::url(),$otoUrls)){{'active menu-open'}}@endif" @if(in_array(Request::url(),$otoUrls)) style="display:block" @endif>               
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
              $arUrls = [route('invoice.generate'), route('invoice.index'), route('aging.index'), route('report.arview'), route('payment.index'),url('period_meter')];
              $glUrls = [route('journal.index'), route('genledger.index'),route('trentry.index'),route('clentry.index'),route('report.glview'),route('report.ytd'),route('report.ledger_view'),route('report.tb_view'),route('report.neraca'),route('report.profitloss')];
              $glSetupUrls = [route('coa.index'),url('department'),url('layouts'),url('department'),url('groupaccount'),url('kurs')];
              $bbUrls = [route('cash_bank.index'),route('bankbook.index'),route('reconcile.index')];
              $tenancyUrls = [route('contract.index'), route('contract.confirmation'), route('contract.addendum'), route('contract.renewal'), route('contract.termination'), route('contract.unclosed')];
              $invUrls = [route('invoice.generate'), route('invoice.index'), route('aging.index'),url('period_meter')];

              $apUrls = [url('accpayable'),url('purchaseorder'),url('treasury'),route('report.apview')];
              $apSetupUrls = [url('supplier')];

              $accreceivables = [52,58,59,63,68,76,35,39,40,41,42,43,44];
              $billingInfos = [35,39,40,41,42,43];
              $generalledger = [64];
              $cashbanks = [72];
              $invoices = [52,58,59];
              $ap = [100];
            ?>
            <li class="treeview @if(in_array(Request::url(),$tenancyUrls) || in_array(Request::url(),$arUrls) || in_array(Request::url(),$glUrls) || in_array(Request::url(),$bbUrls) || Request::url() == url('cost_item') || in_array(Request::url(),$glSetupUrls) || in_array(Request::url(),$apUrls) || in_array(Request::url(),$apSetupUrls)){{'active'}}@endif">
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
                    <ul class="treeview-menu @if(in_array(Request::url(),$arUrls) || in_array(Request::url(),$tenancyUrls) || Request::url() == url('cost_item')){{'active menu-open'}}@endif" @if(in_array(Request::url(),$arUrls) || in_array(Request::url(),$tenancyUrls) || Request::url() == url('cost_item')) style="display:block" @endif>
                      @if(Session::get('role')==1 || !empty(array_intersect($invoices, Session::get('permissions'))) )
                      <li>
                        <a href="#"><i class="fa fa-circle"></i> Invoices
                            <span class="pull-right-container">
                              <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu @if(in_array(Request::url(),$invUrls)){{'active menu-open'}}@endif" @if(in_array(Request::url(),$invUrls)) style="display:block" @endif>
                          @if(Session::get('role')==1 || in_array(52,Session::get('permissions')))
                          <li @if(Request::url() == url('period_meter')) class="active" @endif><a href="{{url('period_meter')}}"><i class="fa fa-circle-o"></i> Meter Input</a></li>
                          @endif
                          @if(Session::get('role')==1 || in_array(58,Session::get('permissions')))
                          <li @if(Request::url() == route('invoice.generate')) class="active" @endif><a href="{{route('invoice.generate')}}"><i class="fa fa-circle-o"></i> Generate Invoice</a></li>
                          @endif
                          @if(Session::get('role')==1 || in_array(59,Session::get('permissions')))
                          <li @if(Request::url() == route('invoice.index')) class="active" @endif><a href="{{route('invoice.index')}}"><i class="fa fa-circle-o"></i> Invoices</a></li>
                          @endif
                        </ul>
                      </li>
                      @endif
                      
                      @if(Session::get('role')==1 || in_array(68,Session::get('permissions')))
                      <li @if(Request::url() == route('payment.index')) class="active" @endif><a href="{{route('payment.index')}}"><i class="fa fa-circle-o"></i> Payment Invoice</a></li>
                      @endif
                      
                      @if(Session::get('role')==1 || in_array(76,Session::get('permissions')))
                      <li @if(Request::url() == route('report.arview')) class="active" @endif><a href="{{route('report.arview')}}"><i class="fa fa-circle-o"></i> Reports</a></li>
                      @endif

                      @if(Session::get('role')==1 || in_array(44,Session::get('permissions')) || !empty(array_intersect($billingInfos, Session::get('permissions'))) )
                      <li>
                        <a href="#"><i class="fa fa-circle"></i> Setup
                            <span class="pull-right-container">
                              <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu @if(in_array(Request::url(),$tenancyUrls) || Request::url() == url('cost_item')){{'active menu-open'}}@endif" @if(in_array(Request::url(),$tenancyUrls) || Request::url() == url('cost_item')) style="display:block" @endif>
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
                              <li @if(Request::url() == route('contract.confirmation')) class="active" @endif><a href="{{route('contract.confirmation')}}"><i class="fa fa-circle-o"></i> Confirmation</a></li>
                              @endif
                              @if(Session::get('role')==1 || in_array(40,Session::get('permissions')))
                              <li @if(Request::url() == route('contract.addendum')) class="active" @endif><a href="{{route('contract.addendum')}}"><i class="fa fa-circle-o"></i> Addendum</a></li>
                              @endif
                              @if(Session::get('role')==1 || in_array(41,Session::get('permissions')))
                              <li @if(Request::url() == route('contract.renewal')) class="active" @endif><a href="{{route('contract.renewal')}}"><i class="fa fa-circle-o"></i> Renewal</a></li>
                              @endif
                              @if(Session::get('role')==1 || in_array(42,Session::get('permissions')))
                              <li @if(Request::url() == route('contract.termination')) class="active" @endif><a href="{{route('contract.termination')}}"><i class="fa fa-circle-o"></i> Termination</a></li>
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
                    <ul class="treeview-menu @if(in_array(Request::url(),$glUrls)){{'active menu-open'}}@endif" @if(in_array(Request::url(),$glUrls) || in_array(Request::url(),$glSetupUrls)) style="display:block" @endif>
                      @if(Session::get('role')==1 || in_array(65,Session::get('permissions')) || in_array(78,Session::get('permissions')) )
                      <li>
                        <a href="#"><i class="fa fa-circle"></i> Periode Processing
                            <span class="pull-right-container">
                              <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu @if(Request::url() == route('trentry.index') || Request::url() == route('clentry.index')){{'active menu-open'}}@endif" @if(Request::url() == route('trentry.index') || Request::url() == route('clentry.index')) style="display:block" @endif>
                          @if(Session::get('role')==1 || in_array(65,Session::get('permissions')))
                          <li @if(Request::url() == route('trentry.index')) class="active" @endif><a href="{{route('trentry.index')}}"><i class="fa fa-circle-o"></i> Transaction Entry</a></li>
                          @endif
                          @if(Session::get('role')==1 || in_array(78,Session::get('permissions')))
                          <li @if(Request::url() == route('clentry.index')) class="active" @endif><a href="{{route('clentry.index')}}"><i class="fa fa-circle-o"></i> Closing Entry</a></li>
                          @endif
                        </ul>
                      </li>
                      @endif

                     
                      @if(Session::get('role')==1 || in_array(77,Session::get('permissions')))
                      <li @if(Request::url() == route('genledger.index')) class="active" @endif><a href="{{route('genledger.index')}}"><i class="fa fa-circle-o"></i> General Ledger</a></li>
                      @endif
                      
                      <li>
                          <a href="#"><i class="fa fa-circle"></i> Report
                              <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                              </span>
                          </a>
                          <ul class="treeview-menu @if(in_array(Request::url(),$glUrls)){{'active menu-open'}}@endif" @if(in_array(Request::url(),$glUrls)) style="display:block" @endif>
                            
                            @if(Session::get('role')==1 || in_array(77,Session::get('permissions')))
                            <li @if(Request::url() == route('report.glview')) class="active" @endif><a href="{{route('report.glview')}}"><i class="fa fa-circle-o"></i> GL List</a></li>
                            @endif
                            @if(Session::get('role')==1 || in_array(77,Session::get('permissions')))
                            <li @if(Request::url() == route('report.ytd')) class="active" @endif><a href="{{route('report.ytd')}}"><i class="fa fa-circle-o"></i> YTD General Ledger</a></li>
                            @endif
                            @if(Session::get('role')==1 || in_array(77,Session::get('permissions')))
                            <li @if(Request::url() == route('report.ledger_view')) class="active" @endif><a href="{{route('report.ledger_view')}}"><i class="fa fa-circle-o"></i> Ledger</a></li>
                            @endif
                            @if(Session::get('role')==1 || in_array(77,Session::get('permissions')))
                            <li @if(Request::url() == route('report.tb_view')) class="active" @endif><a href="{{route('report.tb_view')}}"><i class="fa fa-circle-o"></i> Working Trial Balance</a></li>
                            @endif
                            @if(Session::get('role')==1 || in_array(77,Session::get('permissions')))
                            <li @if(Request::url() == route('report.neraca')) class="active" @endif><a href="{{route('report.neraca')}}"><i class="fa fa-circle-o"></i> Balance Sheet</a></li>
                            @endif
                            @if(Session::get('role')==1 || in_array(77,Session::get('permissions')))
                            <li @if(Request::url() == route('report.profitloss')) class="active" @endif><a href="{{route('report.profitloss')}}"><i class="fa fa-circle-o"></i> Income Statement</a></li>
                            @endif
                            <li><a href="#"><i class="fa fa-circle-o"></i> Income Statement Dept</a></li>

                          </ul>

                          <li>
                              <a href="#"><i class="fa fa-circle"></i> Setup
                                  <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                  </span>
                              </a>
                              <ul class="treeview-menu @if(in_array(Request::url(),$glSetupUrls)){{'active menu-open'}}@endif" @if(in_array(Request::url(),$glSetupUrls)) style="display:block" @endif>
                                @if(Session::get('role')==1 || in_array(11,Session::get('permissions')))
                                <li @if(Request::url() == url('coa')) class="active" @endif><a href="{{url('coa')}}"><i class="fa fa-circle-o"></i> Chart of Account</a></li>
                                @endif
                                <li><a href="#"><i class="fa fa-circle-o"></i> Budget</a></li>
                                @if(Session::get('role')==1 || in_array(1,Session::get('permissions')))
                                <li @if(Request::url() == url('department')) class="active" @endif><a href="{{url('department')}}"><i class="fa fa-circle-o"></i> Department</a></li>
                                @endif
                                @if(Session::get('role')==1 || in_array(11,Session::get('permissions')))
                                <li @if(Request::url() == url('layouts')) class="active" @endif><a href="{{url('layouts')}}"><i class="fa fa-circle-o"></i> Report Layout</a></li>
                                @endif
                                @if(Session::get('role')==1 || in_array(11,Session::get('permissions')))
                                <li @if(Request::url() == url('groupaccount')) class="active" @endif><a href="{{url('groupaccount')}}"><i class="fa fa-circle-o"></i> Group Account</a></li>
                                @endif
                                @if(Session::get('role')==1 || in_array(11,Session::get('permissions')))
                                <li @if(Request::url() == url('kurs')) class="active" @endif><a href="{{url('kurs')}}"><i class="fa fa-circle-o"></i> Kurs</a></li>
                                @endif
                              </ul>
                          </li>
                      </li>

                      
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
                    <ul class="treeview-menu @if(in_array(Request::url(),$bbUrls)){{'active menu-open'}}@endif" @if(in_array(Request::url(),$bbUrls)) style="display:block" @endif>
                      @if(Session::get('role')==1 || in_array(72,Session::get('permissions')))
                      <li @if(Request::url() == route('cash_bank.index')) class="active" @endif><a href="{{route('cash_bank.index')}}"><i class="fa fa-circle-o"></i> Cash Bank List</a></li>
                      @endif
                      @if(Session::get('role')==1 || in_array(72,Session::get('permissions')))
                      <li @if(Request::url() == route('bankbook.index')) class="active" @endif><a href="{{route('bankbook.index')}}"><i class="fa fa-circle-o"></i> Bank Book</a></li>
                      @endif
                      @if(Session::get('role')==1 || in_array(72,Session::get('permissions')))
                      <li @if(Request::url() == route('reconcile.index')) class="active" @endif><a href="{{route('reconcile.index')}}"><i class="fa fa-circle-o"></i> Reconcile Book</a></li>
                      @endif
                    </ul>
                  </li>
                @endif

                @if(Session::get('role')==1 || !empty(array_intersect($ap, Session::get('permissions'))) )
                  <li>
                    <a href="#"><i class="fa fa-circle"></i> Account Payable
                        <span class="pull-right-container">
                          <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu @if(in_array(Request::url(),$apUrls) || in_array(Request::url(),$apSetupUrls)){{'active menu-open'}}@endif" @if(in_array(Request::url(),$apUrls) || in_array(Request::url(),$apSetupUrls)) style="display:block" @endif>
                      @if(Session::get('role')==1 || in_array(100,Session::get('permissions')))
                      <li @if(Request::url() == url('accpayable')) class="active" @endif><a href="{{url('accpayable')}}"><i class="fa fa-circle-o"></i>AP List</a></li>
                      @endif
                      @if(Session::get('role')==1 || in_array(100,Session::get('permissions')))
                      <li @if(Request::url() == url('purchaseorder')) class="active" @endif><a href="{{url('purchaseorder')}}"><i class="fa fa-circle-o"></i> PO List</a></li>
                      @endif
                      @if(Session::get('role')==1 || in_array(100,Session::get('permissions')))
                      <li @if(Request::url() == url('treasury')) class="active" @endif><a href="{{url('treasury')}}"><i class="fa fa-circle-o"></i> AP Payment</a></li>
                      @endif
                       @if(Session::get('role')==1 || in_array(100,Session::get('permissions')))
                      <li @if(Request::url() == route('report.apview')) class="active" @endif><a href="{{route('report.apview')}}"><i class="fa fa-circle-o"></i>Report</a></li>
                      @endif
                      <li>
                          <a href="#"><i class="fa fa-circle"></i> Setup
                              <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                              </span>
                          </a>
                          <ul class="treeview-menu @if(in_array(Request::url(),$apSetupUrls)){{'active menu-open'}}@endif" @if(in_array(Request::url(),$apSetupUrls)) style="display:block" @endif>
                            @if(Session::get('role')==1 || in_array(100,Session::get('permissions')))
                            <li @if(Request::url() == url('supplier')) class="active" @endif><a href="{{url('supplier')}}"><i class="fa fa-circle-o"></i> Supplier</a></li>
                            @endif
                          </ul>
                      </li>
                    </ul>
                  </li>
                @endif
              </ul>
            </li>
        </ul><!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
</aside>
