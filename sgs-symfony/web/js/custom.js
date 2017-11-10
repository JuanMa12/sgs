$(function () {
    //Diccionario de Validacion de Jquery
    jQuery.extend(jQuery.validator.messages, {
        required: "Campo Requerido.",
        email: "Emai no valido.",
        url: "URL no valida.",
        date: "Fecha no valida.",
        dateISO: "Fecha en formato diferente (ISO).",
        number: "Ingrese solo numeros.",
        digits: "Ingrese solo digitos.",
        creditcard: "Numero de tarjeta no valido.",
        equalTo: "Los valores no son iguales.",
        accept: "Extension no permitida.",
        maxlength: jQuery.validator.format("Maximo {0} caracteres."),
        minlength: jQuery.validator.format("Minimo {0} caracteres."),
        rangelength: jQuery.validator.format(" Entre {0} y {1} caracteres."),
        range: jQuery.validator.format("Valor entre {0} and {1}."),
        max: jQuery.validator.format("Valores menores o iguales a {0}."),
        min: jQuery.validator.format("Valores mayores o iguales a {0}.")
    });

    jQuery.validator.setDefaults({ 
        errorClass:'invalid',
        validClass:'valid',
        highlight: function(element, errorClass, validClass) {
            $(element).addClass(errorClass).removeClass(validClass);
        },
        unhighlight: function(element, errorClass, validClass) {
            $(element).removeClass(errorClass).addClass(validClass);
        }
    })

    jQuery.validator.addMethod("letrasyespacio", function(value, element) 
    {  
        return this.optional(element) || /^[a-záéíóúñÁÉÍÓÚÑ," "]+$/i.test(value);
    }, "Solo letras y espacios"); 

    jQuery.validator.addMethod("pwcheck", function(value) { 
        return /\d/.test(value) // y debe tener un digito
        && value.length > 5 // al menos 6 caracteres
    }, "Debe contener al menos 6 caracteres incluyendo digitos y caracteres");

    jQuery.validator.addMethod("sololetras", function(value, element) 
    {  
        return this.optional(element) || /^[a-záéíóúñÁÉÍÓÚÑ]+$/i.test(value);
    }, "Solo letras"); 

    $.extend($.fn.pickadate.defaults, {
        monthsFull: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
        monthsShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        weekdaysFull: ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'],
        weekdaysShort: ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'],
        today: 'Hoy',
        clear: 'Limpiar',
        close: 'Cerrar',
        container: 'body',
        format: "dd-mm-yyyy"
    })

    $.extend( $.fn.dataTable.defaults, {
        "language": {
            "sProcessing":    "Procesando...",
            "sLengthMenu":    "Mostrar _MENU_ registros",
            "sZeroRecords":   "No se encontraron resultados",
            "sEmptyTable":    "Ningún dato disponible en esta tabla",
            "sInfo":          "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty":     "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered":  "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix":   "",
            "sSearch":        "Buscar:",
            "sUrl":           "",
            "sInfoThousands":  ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst":    "Primero",
                "sLast":    "Último",
                "sNext":    "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        },
        
        dom:
            "<'ui two column grid'<'left aligned column'l><'right aligned column'f>>" +
            "<'ui grid'<'column'tr>>" +
            "<'ui two column grid'<'left aligned column'i><'right aligned column'p>>",
        fnDrawCallback: function(settings){

                $('.dropdown-button').dropdown({
                        inDuration: 300,
                        outDuration: 225,
                        constrain_width: false, // Does not change width of dropdown to that of the activator
                        hover: false, // Activate on hover
                        gutter: 0, // Spacing from edge
                        belowOrigin: true // Displays dropdown below the button
                });
            }
    } );

    $('.admin-dropdown-button').dropdown({
        inDuration: 300,
        outDuration: 225,
        constrain_width: false, // Does not change width of dropdown to that of the activator
        hover: true, // Activate on hover
        gutter: 0, // Spacing from edge
        belowOrigin: true, // Displays dropdown below the button
        alignment: 'left' // Displays dropdown with edge aligned to the left of button
    });


    $('.menu-dropdown-button').dropdown({
        inDuration: 300,
        outDuration: 225,
        constrain_width: false, // Does not change width of dropdown to that of the activator
        hover: true, // Activate on hover
        gutter: 0, // Spacing from edge
        belowOrigin: true, // Displays dropdown below the button
        alignment: 'left' // Displays dropdown with edge aligned to the left of button
    });


    $("#app_menu").sideNav({
        menuWidth: 240
    });
})

function toggleButton(selector,message){

    if(message== undefined){
        message = ''
    }

    var element = jQuery(selector)
    var divProgressBar = $('<div class="c-progress"></div>')
    var progressBar = $('<div class="progress"><div class="indeterminate" style="width: 70%"></div></div>')
    
    var divMessage = $(`<span class="right">${message}</span>`)
    divProgressBar.append(progressBar)
    divProgressBar.append(divMessage)

    if(element.is(":visible")){
        element.hide()
        element.after(divProgressBar)
    }else{
        element.show()
        element.next('.c-progress').remove()
    }

    return progressBar

}

function showMaterializeAlert(destiny,color,icon,message){
    var element = $("<div class='card-panel "+color+"'><i class='material-icons left'>"+icon+"</i> "+message+"</div>")
    $(destiny).html(element)
    return element
}

function filterCharacters(data){
    return data.replace(/(<\s*\/?\s*)script(\s*([^>]*)?\s*>)/gi ,'$1jscript$2')
}

function clearInputs(){
    $('.input-field input').each(function(){
        if($(this).val()!=''){
            $(this).next('label').addClass('active')
        }else{
            $(this).next('label').removeClass('active')
        }
    })
}