(function( $ ) {
	$( document ).ready(function(){
		$("input[name='mime_type[]']").on('click',function(event){
			if($("input[name='mime_type[]']:checked").length){
				$('#zym_download').prop('disabled',false);
			}else{
				$('#zym_download').prop('disabled',true);
			}
		});
		
		$('#zym_download').on('click',function(event){
			event.preventDefault();
			$('#zym .loading').show();
			mimes = [];
			$("input[name='mime_type[]']:checked").each(function(){
				mimes.push($(this).val());
			});
			data = {
				action: 'zym_download',
				mimes: mimes
			}
			$.post(ajaxurl,data).done(function(response) {
				window.location.href = zym.url+'dl.php?file='+response.name;
				tpl = '';
				tpl+= '<tr>';
				tpl+= '	<th class="check-column"><input type="checkbox" value="'+response.name+'" name="zym_delete[]" id="zym_delete[]"></th>';
				tpl+= '	<td class="has-row-actions">';
				tpl+= '		<strong><a href="'+(response.url+response.name)+'">'+response.name+'</a></strong>';
				tpl+= '		<div class="row-actions">';
				tpl+= '			<a href="upload.php?page=zym&zym_delete[]='+response.name+'">delete this file</a>';
				tpl+= '		</div>';
				tpl+= '	</td>';
				tpl+= '	<td>'+response.filesize+'</td>';
				tpl+= '</tr>';
				
				$('#zym_tbody').prepend(tpl);
				$('#zym .loading').hide();
				$('#zym .existing_files').removeClass('inactive').addClass('active');
			});
		});
	});
})( jQuery );