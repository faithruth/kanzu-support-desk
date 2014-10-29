<?php global $tab_id; ?>
<div class="ksd-grid-container">
    <div class="ksd-row">

        <div class="ksd-col-2">
            Show: <input type="number" value="5" maxlength="3"  class="ksd-pagination-limit" id="ksd_pagination_limit_<?php echo $tab_id; ?>" max="999" min="1" step="1"/>
        </div>
        <div class="ksd-col-2"></div>
        <div class="ksd-col-2 ksd-ticket-search">
            <input type="type" value="Search..." name="ksd_tkt_search_input_<?php echo $tab_id;?>" class="ksd_tkt_search_input" /> 
            <span class="ksd-tkt-search-btn" id="ksd_tkt_search_btn_<?php echo $tab_id;?>">GO</span>
        </div>
    </div>
</div>

<div class="ksd-grid-container">
    <div id="ticket-list">
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
 

