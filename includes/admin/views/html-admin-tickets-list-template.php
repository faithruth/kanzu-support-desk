<?php global $tab_id; ?>
<div class="ksd-grid-container">
    <div class="ksd-row">

        <div class="ksd-col-2">
            Show: <input type="input" value="5" class="ksd-pagination-limit" id="ksd_pagination_limit_<?php echo $tab_id; ?>"/>
        </div>
        <div class="ksd-col-2"></div>
        <div class="ksd-col-2 ksd-ticket-search">
            <input type="type" value="Search..." name="ksd_tkt_search_input_<?php echo $tab_id;?>" class="ksd_tkt_search_input" /> 
            <span class="ksd-tkt-search-btn" id="ksd_tkt_search_btn_<?php echo $tab_id;?>">GO</span>
        </div>
    </div>
</div>

<div class="ksd-grid-container">
    <div class="ksd-row">
        <table class="ksd-table" border="0" cellspacing="0" >
            <thead>
            <th class="">
                    <input type="checkbox" class="tkt_chkbx_all" id="tkt_chkbx_all_<?php echo $tab_id;?>" checked="">
            </th>
            <th class="">Subject</th>
            <th class="">Channel</th>
            <th class="">Logged by</th>
            <th class="">Time logged</th>
            </thead>
            <tbody id="ticket-list">
            </tbody>
                
        </table>
    </div>
</div>    
  


<div class="ksd-grid-container">
    <div class="ksd-row">
        <div class="ksd-col-6 ksd-ticket-nav">
            <nav id="ksd_pagination_<?php echo $tab_id;?>">  
                <ul>  
                    <!-- Pagination -->
                </ul>  
            </nav>  
        </div>
    </div>
</div>


