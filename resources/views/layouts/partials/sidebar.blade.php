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

            <li class="treeview">
              <a href="#">
                <i class="fa fa-building-o"></i> <span>TENANCY</span>
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul class="treeview-menu">
                <li>
                  <a href="#"><i class="fa fa-circle"></i> Contract
                    <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <li><a href="{{route('contract.index')}}"><i class="fa fa-circle-o"></i> Contract</a></li>
                    <li><a href="{{route('contract.confirmation')}}"><i class="fa fa-circle-o"></i> Contract Confirmation</a></li>
                    <li><a href="{{route('contract.addendum')}}"><i class="fa fa-circle-o"></i> Contract Addendum</a></li>
                    <li><a href="{{route('contract.renewal')}}"><i class="fa fa-circle-o"></i> Contract Renewal</a></li>
                    <li><a href="{{route('contract.termination')}}"><i class="fa fa-circle-o"></i> Contract Termination</a></li>
                  </ul>
                </li>
                <li>
                    <a href="#"><i class="fa fa-circle"></i> Units
                      <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                      </span>
                    </a>
                    <ul class="treeview-menu">
                      <li><a href="{{ url('unit') }}"><i class="fa fa-circle-o"></i> Unit</a></li>
                      <li><a href="{{ url('unittype') }}"><i class="fa fa-circle-o"></i> Unit Type</a></li>
                      <li><a href="{{route('floor.index')}}"><i class="fa fa-circle-o"></i> Floor</a></li>

                    </ul>
                </li>
                <li><a href="{{url('cost_item')}}"><i class="fa fa-circle-o"></i> Cost Items</a></li>
                <li><a href="{{ url('tenant') }}"><i class="fa fa-circle-o"></i> Tenant/Owner</a></li>
                <li><a href="{{url('typetenant')}}"><i class="fa fa-circle-o"></i> Tenant Type</a></li>
                <li><a href="{{route('marketing.get')}}"><i class="fa fa-circle-o"></i> Marketing/Agent</a></li>
                <li><a href="{{ url('typetenant') }}"><i class="fa fa-circle-o"></i> Owner/Tenant Type</a></li>
                <li><a href="{{ url('vaccount') }}"><i class="fa fa-circle-o"></i> Virtual Account</a></li>
              
              </ul>
            </li>


            <li class="treeview">
              <a href="#">
                <i class="fa fa-fire-extinguisher"></i> <span>ENGINEERING</span>
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul class="treeview-menu">
                <li><a href="{{url('period_meter')}}"><i class="fa fa-circle-o"></i> Meter Input</a></li>
                <li><a href="{{ url('complaint') }}"><i class="fa fa-circle-o"></i> Complain Entry</a></li>
              </ul>
            </li>

            <!-- <li class="treeview">
              <a href="#">
                <i class="fa fa-gears"></i> <span>FILE</span>
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul class="treeview-menu">
                <li>
                  <a href="#"><i class="fa fa-circle"></i> Menu
                    <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <li><a href="#"><i class="fa fa-circle-o"></i> Master Menu</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Master Authorization</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> User Authorization</a></li>
                  </ul>
                </li>
                <li><a href="#"><i class="fa fa-circle-o"></i> System Configuration</a></li>
                <li><a href="#"><i class="fa fa-circle-o"></i> Printer Setup</a></li>
              </ul>
            </li> -->

            

            

            <li class="treeview">
              <a href="#">
                <i class="fa fa-book"></i> <span>ACCOUNT RECEIVABLES</span>
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul class="treeview-menu">
                <li><a href="{{route('invoice.generate')}}"><i class="fa fa-circle-o"></i> Generate Invoice</a></li>
                <li><a href="{{route('invoice.index')}}"><i class="fa fa-circle-o"></i> Invoices</a></li>
                <li><a href="#"><i class="fa fa-circle-o"></i> Aging Invoices</a></li>
                <li><a href="#"><i class="fa fa-circle-o"></i> Outstanding</a></li>
                <!-- <li>
                  <a href="#"><i class="fa fa-circle"></i> Form
                    <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <li><a href="#"><i class="fa fa-circle-o"></i> Invoice</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Invoice (PPN)</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Invoice Email</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Tax</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Reminder Letter</a></li>
                  </ul>
                </li> -->
                <!-- <li>
                  <a href="#"><i class="fa fa-circle"></i> Report
                    <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <li><a href="#"><i class="fa fa-circle-o"></i> List Future Billing</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Invoice Detail</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Invoice Cancel</a></li>
                    <li>
                      <a href="#"><i class="fa fa-circle"></i> Outstanding
                        <span class="pull-right-container">
                          <i class="fa fa-angle-left pull-right"></i>
                        </span>
                      </a>
                      <ul class="treeview-menu">
                        <li><a href="#"><i class="fa fa-circle-o"></i> Outstanding Invoice</a></li>
                        <li><a href="#"><i class="fa fa-circle-o"></i> Outstanding Contract</a></li>
                        <li><a href="#"><i class="fa fa-circle-o"></i> Outstanding Payment</a></li>
                        <li><a href="#"><i class="fa fa-circle-o"></i> A/R Card</a></li>
                      </ul>
                    </li>
                    <li>
                      <a href="#"><i class="fa fa-circle"></i> Aging
                        <span class="pull-right-container">
                          <i class="fa fa-angle-left pull-right"></i>
                        </span>
                      </a>
                      <ul class="treeview-menu">
                        <li><a href="#"><i class="fa fa-circle-o"></i> Aging Invoice</a></li>
                        <li><a href="#"><i class="fa fa-circle-o"></i> Aging Invoice Detail</a></li>
                      </ul>
                    </li>
                  </ul>
                </li>
  -->               <!-- <li>
                  <a href="#"><i class="fa fa-circle"></i> Utility
                    <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
                  </a> -->
                  <!-- <ul class="treeview-menu">
                    <li><a href="#"><i class="fa fa-circle-o"></i> Cancel Invoice</a></li>
                  </ul> -->
                <!-- </li>
                <li>
                  <a href="#"><i class="fa fa-circle"></i> Setup
                    <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <li><a href="#"><i class="fa fa-circle-o"></i> Type Of Bill</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Fine/Denda Settings</a></li>
                  </ul>
                </li> -->
              </ul>
            </li>

            <li class="treeview">
              <a href="#">
                <i class="fa fa-book"></i> <span>ACCOUNT PAYABLE</span>
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul class="treeview-menu">
                <li><a href="#"><i class="fa fa-circle-o"></i> Purchase Order</a></li>
                <li><a href="#"><i class="fa fa-circle-o"></i> Receiving</a></li>
                <li>
                  <a href="#"><i class="fa fa-circle"></i> Report
                    <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <li><a href="#"><i class="fa fa-circle-o"></i> Outstanding Payable</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Inventory Report</a></li>
                  </ul>
                </li>
                <li>
                  <a href="#"><i class="fa fa-circle"></i> Setup
                    <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <li><a href="{{ url('supplier') }}"><i class="fa fa-circle-o"></i> Supplier</a></li>
                  </ul>
                </li>
              </ul>
            </li>

            <li class="treeview">
              <a href="#">
                <i class="fa fa-gears"></i> <span>MASTER DATA</span>
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul class="treeview-menu">
                <li><a href="{{url('coa')}}"><i class="fa fa-circle-o"></i> Chart of Account</a></li>
                <li><a href="{{url('currency')}}"><i class="fa fa-circle-o"></i> Currency</a></li>
                <li><a href="{{url('contractstatus')}}"><i class="fa fa-circle-o"></i> Contract Status</a></li>
                <li><a href="{{url('department')}}"><i class="fa fa-circle-o"></i> Department</a></li>
                <li><a href="{{url('invtype')}}"><i class="fa fa-circle-o"></i> Invoice Type</a></li>
                <li><a href="{{url('groupaccount')}}"><i class="fa fa-circle-o"></i> Group Account</a></li>
                <li><a href="{{url('groupaccdetail')}}"><i class="fa fa-circle-o"></i> Group Account Detail</a></li>
                <li><a href="{{url('rentalperiod')}}"><i class="fa fa-circle-o"></i> Rental Period</a></li>
                <li><a href="{{url('supplier')}}"><i class="fa fa-circle-o"></i> Supplier</a></li>
              </ul>
            </li>

            <li class="treeview">
              <a href="#">
                <i class="fa fa-bank"></i> <span>CASH BANK</span>
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul class="treeview-menu">
                <li><a href="#"><i class="fa fa-circle-o"></i> OR Entry</a></li>
                <li><a href="#"><i class="fa fa-circle-o"></i> Advance Entry</a></li>
                <li><a href="#"><i class="fa fa-circle-o"></i> Unidentified Entry</a></li>
                <li><a href="#"><i class="fa fa-circle-o"></i> Virtual Account Entry</a></li>
                <li><a href="#"><i class="fa fa-circle-o"></i> Voucher IN</a></li>
                <li><a href="#"><i class="fa fa-circle-o"></i> Voucher OUT</a></li>
                <li><a href="#"><i class="fa fa-circle-o"></i> Mutasi Account</a></li>
                <li><a href="#"><i class="fa fa-circle-o"></i> Update Giro Out</a></li>
                <li><a href="#"><i class="fa fa-circle-o"></i> Receipt Deposit</a></li>
                <li><a href="#"><i class="fa fa-circle-o"></i> Refund Deposit</a></li>
                <li>
                  <a href="#"><i class="fa fa-circle"></i> Report
                    <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <li><a href="#"><i class="fa fa-circle-o"></i> List Payment</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> List Payment Detail</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> List Payment Canceled</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> List Advance</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> List Cash Bank</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> List Virtual Account</a></li>
                  </ul>
                </li>
                <li>
                  <a href="#"><i class="fa fa-circle"></i> Utility
                    <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <li><a href="#"><i class="fa fa-circle-o"></i> Clearance OR</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Spilt Advance</a></li>
                  </ul>
                </li>
                <li>
                  <a href="#"><i class="fa fa-circle"></i> Setup
                    <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <li><a href="#"><i class="fa fa-circle-o"></i> Cash Flow</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Master Bank</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Cash Flow</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Master Transaction Type</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Master Transaction</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Master Pph</a></li>
                  </ul>
                </li>
              </ul>
            </li>

            <li><a href="{{route('journal.index')}}"><i class="fa fa-circle-o"></i> Journal Entry</a></li>

            <li class="treeview">
              <a href="#">
                <i class="fa fa-archive"></i> <span>Report</span>
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul class="treeview-menu">
                <li><a href="#"><i class="fa fa-circle-o"></i> General Ledger</a></li>
                <li><a href="#"><i class="fa fa-circle-o"></i> Profit and Loss</a></li>
                <li><a href="#"><i class="fa fa-circle-o"></i> Trial Balance</a></li>
                <li><a href="#"><i class="fa fa-circle-o"></i> Balance Sheet</a></li>
                <li><a href="#"><i class="fa fa-circle-o"></i> Budget</a></li>
                <!-- <li>
                  <a href="#"><i class="fa fa-circle"></i> Posting
                    <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <li><a href="#"><i class="fa fa-circle-o"></i> Posting Invoice</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Posting OR</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Posting Advance</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Posting Untrack Receipt</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Posting Credit Note</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Posting Cash Bank Out</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Posting Cash Bank In</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Posting Bank Mutation</a></li>
                  </ul>
                </li>
                <li><a href="#"><i class="fa fa-circle-o"></i> Amortization</a></li>
                <li><a href="#"><i class="fa fa-circle-o"></i> Credit Note</a></li>
                <li>
                  <a href="#"><i class="fa fa-circle"></i> Setup
                    <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <li><a href="#"><i class="fa fa-circle-o"></i> Period</a></li>
                    <li><a href="{{ route('coa.index') }}"><i class="fa fa-circle-o"></i> Chart Of Account</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Balance Sheet</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Journal Posting Billing</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Type Of Journal</a></li>
                  </ul>
                </li> -->
              </ul>
            </li>

            

        </ul><!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
</aside>
