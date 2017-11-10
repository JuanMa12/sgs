(function($){

    //object constructor
    var AutocompleteList = function(element,params){
    
        //private atributes
        var autocompletePath = params.path
        var formName = params.form_name

        var mainElement = $(`    
            <table class="striped">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr class="form">
                        <td colspan="2">
                            <input type="hidden" class="search_alist_code">
                            <input type="text" class="search_alist_text">
                        </td>
                    </tr>
                </tfoot>
            </table>`)

        var tbody = mainElement.find('tbody')
        var code = mainElement.find('.search_alist_code')
        var textField = mainElement.find('.search_alist_text')

        $(element).html(mainElement)

        ///////////


        textField.autocomplete_default({
            delay: 500,
            minLength: 3,
            source:function(request, response){
                $.post(autocompletePath,{word:request.term},function(data){

                    if(data.success == true){
                        response(data.description)
                    }
                })
            },
            select: function( event, ui ) {
                $(this).val(ui.item.label);
                code.val(ui.item.value);
                addInAction()
                return false;
            },
            close: function( event, ui ) {
                if(code.val()==''){
                    $(this).val('');
                }
            }
        })

        function addInAction(){
            var name = textField.val()
            var id = code.val()

            if($(`input[name='${formName}[]'][value='${id}']`).length == 0 && id != ''){
                addElement(id,name)
            }

            textField.val("")
            code.val("")
        }

        function addElement(id,name){
            var element = $("<tr class='item'></tr>")

            element.append(`<td>${name}</td>`)

            var removeButton = $("<td><a class='btn-floating red'><i class='material-icons'>remove</i></a></td>")

            removeButton.on('click',function(){
                element.remove()
            })

            var registry = $(`<input type="hidden" name="${formName}[]" value="${id}">`)

            element.append(removeButton)
            element.append(registry)

            tbody.append(element)
        }
    }

    //front object managment extention
    $.fn.autocompleteList = function(params){
        return this.each(function(){
            var element = $(this);
            if (element.data('autocompleteList')){
                var autocompleteList = element.data('autocompleteList');
            }else{
                var autocompleteList = new AutocompleteList(this,params);
                element.data('autocompleteList', autocompleteList);
            }
            return autocompleteList;
        });
    }

})(jQuery)