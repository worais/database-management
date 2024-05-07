function addWhereRow(){
    const index = jQuery('#where-div .where-condition').length;
    
    jQuery('.where-btn-apply').before(`<div class="where-condition" for="${index}">
        <input type="text" class="value"/>
        <button class='condition-btn AND'>AND</button>
        <button class='condition-btn OR'>OR</button>
    </div>`);    
}

window.onload = function() {
    window.editor = CodeMirror.fromTextArea(document.getElementById('sql'), {
        mode: 'text/x-mariadb',
        indentWithTabs: true,
        smartIndent: true,
        lineNumbers: true,
        matchBrackets : true,
        autofocus: true,
        extraKeys: {"Ctrl-Space": "autocomplete"},
        hintOptions: {tables: {
        users: ["name", "score", "birthDate"],
        countries: ["name", "population", "size"]
        }}
    });

    window.editor.on("change", function() {
        jQuery('input[name=colunms]').val('');

        window.offset_input = false;
    });    

    window.onhashchange = function (){
        if(window.location.hash == ''){
            window.editor.getDoc().setValue('');
            jQuery('#btn-sql-run').click();
            return false;
        }

        params = new URLSearchParams( window.location.hash.substr(1) );
        if(params.has("query") && params.get("query") != window.sql){
            window.editor.getDoc().setValue( atob( params.get("query") ) );
            jQuery('#btn-sql-run').click();
        }
    };

    setTimeout(() => {
        window.onhashchange();
    }, 1);    

    jQuery('#btn-sql-run').click(function(){
        const $btn = jQuery(this);
        const data = { 
            action: 'worais-database-query', 
            sql: btoa( window.editor.getDoc().getValue() ),
            where: window.where,
            offset: window.offset_input,
            limit: window.limit
        };

        jQuery('#worais-database').find('input, select').each(function(x, field) {
            if(field.type == 'checkbox'){
                data[field.name] = (field.checked)? 1 : 0;
            } else {
                data[field.name] = field.value;
            }
        });

        const input = window.editor.getDoc().getValue();
        if(input != ''){
            window.location.hash = 'query=' + btoa( input );
        }

        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data,                                         
            beforeSend:function() {
                $btn.addClass('loading');
                $btn.removeClass('error');
            },                
            success:function(data) {
                jQuery('#results').html(data);

                if(typeof window.sql == 'string'){
                    window.editor.getDoc().setValue( atob( window.sql ) );
                    window.location.hash = 'query=' + window.sql;
                }
                
                //colunms control
                jQuery('#colunms-div, #where-div, #tools').html('');
                jQuery('.where-btn, #where-div, .colunms-btn, #colunms-div').removeClass("open");
                if(window.total > window.limit){
                    jQuery('#tools').append(`<div class='pagination'><a class="pagination-prev"></a><a class="pagination-next"></a></div>`);
                    if(window.offset == 0){
                        jQuery('#tools .pagination-prev').addClass("disabled");
                    }
                    if(window.offset + window.limit > window.total){
                        jQuery('#tools .pagination-next').addClass("disabled");
                    }                    
                }

                if(window.colunms){
                    jQuery('#tools').append(`<a class='colunms-btn'></a>`);

                    for (col of Object.keys(window.colunms)){
                        const cla = (window.colunms[col].active !== false)? 'active' : '';
                        jQuery('#colunms-div').append(`<button class="${cla}">${col}</button>`);
                    }
                    jQuery('#colunms-div').append(`<br /><button class='colunms-btn-apply'>Apply</button>`);

                    jQuery('.colunms-btn').html( jQuery('#colunms-div button.active').length );
                    jQuery('.colunms-btn').click(function(){
                        jQuery('.where-btn,#where-div').removeClass("open");

                        jQuery(this).toggleClass("open");
                        jQuery('#colunms-div').toggleClass("open");
                    });                  

                    jQuery('#colunms-div button:not(.colunms-btn-apply)').click(function(){
                        jQuery(this).toggleClass("active");
                    });

                    jQuery('.colunms-btn-apply').click(function(){
                        const colunms = [];
                        jQuery('#colunms-div button.active').each(function(x, element) {
                            colunms[x] = element.innerText;
                        });
                        jQuery('input[name=colunms]').val(colunms.join(','));

                        jQuery('#btn-sql-run').click();
                    });

                    //filter
                    jQuery('#tools').append(`<a class='where-btn'></a>`);
                    jQuery('.where-btn').click(function(){
                        jQuery('.colunms-btn, #colunms-div').removeClass("open");

                        jQuery(this).toggleClass("open");
                        jQuery('#where-div').toggleClass("open");
                    });     

                    jQuery('#where-div').append(`<button class='where-btn-apply'>Apply</button>`);
                    if(Array.isArray(window.where) && window.where.length > 0){
                        let i = 0;
                        for (condition of window.where){
                            if(!condition.isOperator){
                                let condtionHTML = `<div class="where-condition" for="${i}">
                                    <input type="text" class="value" value="${condition.expr}"/>

                                    <button class='condition-btn AND'>AND</button>
                                    <button class='condition-btn OR'>OR</button>                            
                                </div>`;

                                jQuery('.where-btn-apply').before(condtionHTML);
                                i++;
                            }else{
                                jQuery(`.where-condition[for=${i-1}] .condition-btn.${condition.expr}`).addClass("selected");
                            }

                            jQuery('.where-btn').html( jQuery('#where-div .where-condition').length );
                        }
                    }else{
                        addWhereRow();
                    }                    

                    jQuery('#tools').append(`<i class='table-btn'>${window.table}(${window.total})</i>`);
                }
            },   
            error:function(xhr, ajaxOptions, thrownError) {
                jQuery('#results').html(`<div class='error'>${thrownError}</div>`);
            },
            complete:function() {
                $btn.removeClass('loading');
            }                                                             
        })    
    })   
    
    jQuery("#where-div").on( "click", ".condition-btn", function() {
        jQuery(this).parent().find('.selected').removeClass('selected');
        jQuery(this).addClass('selected');

        const index = parseInt( jQuery(this).parent().attr('for') );
        if(index == jQuery('#where-div .where-condition').length - 1){            
            addWhereRow();

            jQuery('.where-btn').html( jQuery('#where-div .where-condition').length );
        }        
    });    

    jQuery("#where-div").on( "click", ".where-btn-apply", function() {
        window.where = { build: true, conditions: [] };
        jQuery('#where-div input, #where-div button.selected').each(function(x, element) {
            where.conditions[x] = (jQuery(this).val() || jQuery(this).html().trim())
        });

        jQuery('#btn-sql-run').click();
    }); 

    jQuery("#results").on( "click", ".row-edit", function() {
        const data = window.data[ jQuery(this).attr('for') ];

        jQuery('#row-form').html('');
        for (col of Object.keys(window.colunms)){
            if(window.colunms[col].active){
                const cla = (window.colunms[col].key == 'PRI')? 'primary' : '';
                jQuery('#row-form').append(`<label class="${cla}" for="${col}">${col} <i>${window.colunms[col].type}</i><input name="${col}"/></label>`);
                jQuery(`#row-form label[for="${col}"] input`).val(data[col]);
            }
        }
        jQuery('#row-form').append('<a class="cancel-btn">Cancel</a><button class="btn-lg" id="row-edit-dtn"><span class="spinner is-active"></span>Save</button>');
    }); 

    jQuery("#tools").on( "click", ".pagination-prev", function() {
        window.offset_input = window.offset - window.limit;
        if(window.offset_input < 0){
            window.offset_input = 0;
        }

        jQuery('#btn-sql-run').click();
    }); 
    jQuery("#tools").on( "click", ".pagination-next", function() {
        window.offset_input = window.offset + window.limit;
        jQuery('#btn-sql-run').click();
    });     


    jQuery("#row-form").on( "click", ".cancel-btn", function() {
        jQuery('#row-form').html("");
    }); 

    jQuery("#row-form").on( "click", "#row-edit-dtn", function() {
        let data = { 
            action: 'worais-database-update', 
            table: window.table,
            data: {},            
            primary: {}
        };

        jQuery('input[name="_wpnonce"], input[name="_wp_http_referer"]').each(function(x, field) {
            data[field.name] = field.value;
        });
        jQuery('#row-form label.primary input').each(function(x, field) {
            data['primary'][field.name] = field.value;
        }); data['primary'] = btoa(JSON.stringify( data['primary'] ));
        jQuery('#row-form label:not(.primary) input').each(function(x, field) {
            data['data'][field.name] = field.value;
        }); data['data'] = btoa(JSON.stringify( data['data'] ));

        const $btn = jQuery(this);
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data,                                         
            beforeSend:function() {
                $btn.addClass('loading');
                $btn.removeClass('error');
            },                
            success:function(data) {
                jQuery('#row-form').html(data);
                if(data == ''){
                    jQuery('#btn-sql-run').click();
                }
            },
            error:function(xhr, ajaxOptions, thrownError) {
                jQuery('#row-form').html(`<div class='error'>${thrownError}</div>`);
            },
            complete:function() {
                $btn.removeClass('loading');
            }            
        });
    });     
};