$(function () {

    $.widget("custom.autocomplete_default", $.ui.autocomplete, {
        _create: function(){
            this._super();
            this.widget().menu("option", "items", "> :not(.ui-autocomplete-category)");
        },
        _renderMenu: function(ul, items) {

            var that = this

            $.each(items, function(index, item) {

                li = that._renderItemData(ul, item);
                
                if (item.states) {
                    li.attr("aria-label", item.states + " : " + item.label);
                }

            });
        },
        _renderItemData: function( ul, item ) {
            return $("<li class='ui-autocomplete-item z-depth-1'></li>")
                .data("ui-autocomplete-item", item)
                .append(item.label)
                .appendTo(ul);
        }  
    });

    $.widget("custom.autocomplete_tree", $.ui.autocomplete, {
        _create: function(){
            this._super();
            this.widget().menu("option", "items", "> :not(.ui-autocomplete-category)");
        },
        _renderMenu: function(ul, items) {

            var that = this
            var currentCategory = ""

            for(var item of items){

                if(currentCategory != item.category){
                    ul.append("<li class='ui-autocomplete-category z-depth-1'><span class='glyphicon glyphicon-map-marker'> " + item.category + "</li>");
                    currentCategory = item.category;
                }

                li = that._renderItemData(ul, item);
                
                if (item.category) {
                    li.attr("aria-label", item.category + " : " + item.label);
                }

            }
        },
        _renderItemData: function( ul, item ) {
            return $("<li class='ui-autocomplete-item z-depth-1'></li>")
                .data("ui-autocomplete-item", item)
                .append(item.label)
                .appendTo(ul);
        }  
    });
})