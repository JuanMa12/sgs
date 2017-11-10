(function($){

    //object constructor
    var AutocompleteTree = function(element,params){
    
        //private atributes
        var infoPath = params.infoPath
        var arrPath = params.autocompletePath
        var fieldName = (params.fieldName)?params.fieldName:'item'
        var formName = params.form_name

        var mainElement = $(`    
            <div class="row">
                <div class="input-field col s12">
                    <input class="atree-search" type="text" placeholder="Busca y agrega">
                </div> 
                <div class="atree-content col s12 scroll-items"></div>
            </div>
            `)

        var textField = mainElement.find('.atree-search')
        var content = mainElement.find('.atree-content')

        $(element).html(mainElement)
        getField()

        function getField(){
            textField.autocomplete_tree({
                delay: 500,
                minLength: 3,
                source:function(request, response){
                    var wargs = []
                    for(var url of arrPath){
                        wargs.push($.post(url,{word:request.term}))
                    }

                    $.when.apply($,wargs).done(function(){
                            var arrItems  = []
                            for(var rdata of arguments){
                                var data = rdata[0]
                                if (data == undefined) {
                                    data = rdata    
                                }
                                
                                if(data.success == true){
                                    for(var item of data.description){
                                        item.category = data.title
                                        arrItems.push(item)
                                    }
                                }
                            }
                            response(arrItems)
                    })

                },
                select: function( event, ui ) {
                    //$(this).val(ui.item.label);
                    getInfo(ui.item)
                    return false;
                },
                close: function(){
                    $(this).val("");
                }
            })
        }

        function getInfo(item){
            $.post(infoPath,{id:item.value,type:item.category},function(data){
                if(data.success){                        
                    var itemContent = $("<div class='row'>")
                    itemContent.prepend(renderItem(data.description))
                    content.prepend(itemContent)
                }
            })
        }

        var idLabelKey = 0

        function renderItem(item){
            idLabelKey++
            
            var icontent = $("<div>")
            var labelname = item.name
            var tooltip = false

            if(labelname.length > 30){
                labelname = labelname.substring(0,30)+'...'
            }

            var jsItem = $(`
                <p>
                    <input type="checkbox" id="${idLabelKey}"/>
                    <label for="${idLabelKey}">${labelname}</label>
                </p>
                `)

            var check = jsItem.find("input[type='checkbox']")

            var icon = $(`<i class="material-icons left">add</i>`)
            icon.css('cursor','pointer')

            var jsChilds = $("<div style='margin-left:20px'>")

            icon.on('click',function(){
                if(icon.html() != 'label_outline'){
                    if(icon.html() == 'add'){
                        jsChilds.show()
                        icon.html('remove')
                    }else{
                        icon.html('add')
                        jsChilds.hide()
                    }
                }
            })

            jsItem.append(icon)
            icontent.append(jsItem)
    
            if(item.childs){
                for(var citem of item.childs){
                    jsChilds.append(renderItem(citem))
                }
                icontent.append(jsChilds)
                jsChilds.hide()

                check.on('change',function(){
                    var childs = jsChilds.find("input[type='checkbox']")
                    if(check.prop('checked')){
                        childs.prop('checked',true)
                    }else{
                        childs.prop('checked',false)
                    }
                })

            }else{
                icon.html('label_outline')
                check.attr('name', `${fieldName}[]`)
                check.attr('value',item.id)
            }

            check.prop('checked','checked')
            return icontent

        }

        this.reset = function(){
            content.html('')
        }

        this.refreshData = function(data){
            arrPath = data
            getField()
        }

        this.drawItem = function(data){
            var itemContent = $("<div class='row'>")
            itemContent.append(renderItem(data))
            content.append(itemContent)
        }
    }

    //front object managment extention
    $.fn.autocompleteTree = function(params){
        return this.each(function(){
            var element = $(this);
            if (element.data('autocompleteTree')){
                var autocompleteTree = element.data('autocompleteTree');
            }else{
                var autocompleteTree = new AutocompleteTree(this,params);
                element.data('autocompleteTree', autocompleteTree);
            }
            return autocompleteTree;
        });
    }

})(jQuery)