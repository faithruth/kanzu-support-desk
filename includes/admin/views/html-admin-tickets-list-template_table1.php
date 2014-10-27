<div class="ksd-grid-container">
    <div class="ksd-row">

        <div class="ksd-col-2"></div>
        <div class="ksd-col-2"></div>
        <div class="ksd-col-2 ksd-ticket-search">
            <input type="type" value="Search..." /> <span>GO</span>
        </div>
    </div>
</div>

<div id="select-all-tickets">
 
      <input type="checkbox" id="tkt_chkbx_all" checked=""><span><?php _e('All ','kanzu-support-desk'); ?></span>
	
		<div class="ticket-actions" id="tkt_all_options">
			<a href="#" class="trash" id="tkt_'+value.tkt_id+'">Trash</a> | 
			<a href="#" id="tkt_'+value.tkt_id+'" class="change_status">Change Status</a> | 
			<a href="#" id="tkt_'+value.tkt_id+'" class="assign_to">Assign To</a>
		</div>
	
	  
</div>
<div id="ticket-list" >
</div>


<div class="ksd-grid-container">
    <div class="ksd-row">
        <div class="ksd-col-6 ksd-ticket-nav">
            <nav>  
                <ul>  
                    <li><a rel="external" href="#"><<</a></li>  
                    <li><a rel="external" href="#"><</a></li>  
                    <li><a rel="external" href="#">3</a></li>  
                    <li><a rel="external" href="#">4</a></li>  
                    <li><a rel="external" href="#" class="current-nav">5</a></li>  
                    <li><a rel="external" href="#">6</a></li>  
                    <li><a rel="external" href="#">7</a></li>  
                    <li><a rel="external" href="#">></a></li>  
                    <li><a rel="external" href="#">>></a></li>  
                </ul>  
            </nav>  
        </div>
    </div>
</div>