(function($){

    var errorReportModal = $(`
        <div class="modal modal-fixed-footer">
            <div class="modal-content">
                <h4 class="teal-text">Listado de incidencias</h4>
                <div class="progress"><div class="indeterminate" style="width: 70%"></div></div>
                <table class="table">
                    <thead>
                        <tr>
                            <td>Fecha</td>
                            <td>Id del proceso</td>
                            <td>Mensaje</td>
                        </tr>       
                    </thead>
                    <tbody>
                </table>
            </div>
            <div class="modal-footer">
                <a class="btn" id="btn_dowland_report_error">Descargar Reporte</a>
            </div>
        </div>`)


    var paramsReportModal = $(`
        <div class="modal">
            <div class="modal-content">
                <h4 class="teal-text">Listado de Parametros <span></span></h4>
                <table class="table">
                    <thead>
                        <tr>
                            <td>Parametro</td>
                            <td>Valor</td>
                        </tr>       
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>`)


    var visibilityReportModal = $(`
        <div class="modal">
            <div class="modal-content">
                <h4 class="teal-text">Perfiles de Visibilidad <span id="name_process_order"></span></h4>
                <div class="progress"><div class="indeterminate" style="width: 70%"></div></div>
                <form id="visibility_form">            
                    <input type="hidden" id="profile_process_order" name="process_order_id" value="">             
                    <div id="profiles_visibility" class="row col m12">
                </form>
                <div id="visibility_form_message"></div> 
            </div>
            <div class="modal-footer">
                <button id="btn_save_visibility" type="button" class="btn orange">Guardar</button>
            </div>
        </div>`)

    $('body').append(errorReportModal)
    $('body').append(paramsReportModal)
    $('body').append(visibilityReportModal)

    errorReportModal.modal({
        ready: clearInputs,
    })

    paramsReportModal.modal({
        ready: clearInputs,
    })

    visibilityReportModal.modal({
        ready: clearInputs,
        complete:function(){
            document.getElementById('visibility_form').reset();
        }
    })

    $("#btn_save_visibility").on('click',function(){
      $("#visibility_form").submit()
    })

    $("#visibility_form").validate({
        ignore:'.ignore',
        submitHandler:function(form){
            toggleButton("#btn_save_visibility")
            $.post('/process/visibility/save',$(form).serialize(), function(data){
                toggleButton("#btn_save_visibility")

                $("#visibility_form_message").html('')

                if (data.success == true) {
                    visibilityReportModal.modal('close')
                    Materialize.toast("Se han guardado los datos", 4000)
                }else{
                    Materialize.toast(data.description, 4000)
                }

            })
        }
    })

    /**
     * Constructor de la clase ReportModal
     * @param {Objsect} element 
     * @param {Object} params  
     */
    var ReportOrder = function(element,params){
    
        //private atributes
        var name = params.name
        var date = params.date
        var status = params.status
        var progress = params.progress
        var id = params.id
        var errors = params.errors
        var path = params.path
        var pathDelete = params.pathDelete
        var pathHomeReport = params.pathHomeReport
        var paramsProcess = params.params
        var actions = params.actions
        var pathGetVisibility = params.pathGetVisibility
        var pathSalientSave = params.pathSalientSave
        var salient = params.salient
        var type = params.type
        var typeName = params.typeName
        var autor = params.autor
        var messageHomologation = params.messageHomologation

        var engineMessage = ''

        var star= 'star_border'
    
        //extrae la informacion de los errores y los pinta en una ventana modal    
        var getInfoProcessError = function(){
            var tbody = errorReportModal.find("tbody")
            tbody.html("")//se limpia la tabla de errores

            errorReportModal.find('.progress').show()
            errorReportModal.find('table').hide()
            errorReportModal.modal('open')

            $.post(path,function(data){
                if(data.success == true){
                    tbody.html(data.description.html)
                    errorReportModal.find('a').attr('href',data.description.pathDowland);
                }

                errorReportModal.find('.progress').hide()
                errorReportModal.find('table').show()
            })
        } 

        //Guarda los reportes favoritos del usuario     
        var getSalientSaveProcess = function(){

            var interval = setInterval(function(){
                if(divDetails.find('i').html() == 'star_border'){
                    divDetails.find('i').html('star')
                }else{
                    divDetails.find('i').html('star_border')
                }
            },400)
            
            $.post(pathSalientSave,{_id:id},function(data){
                clearInterval(interval)

                if(data.success == true){


                    if (data.description == 1) {
                        divDetails.find('i').html('star_border')
                    }else{
                        divDetails.find('i').html('star')
                    }
                }else{
                    Materialize.toast(data.description, 4000)
                }
            })
        }
        
        //extrae la informacion de los errores y los pinta en una ventana modal    
        var getInfoProcessParams = function(){
            var tbody = paramsReportModal.find("tbody")
            tbody.html("")

            $.each(paramsProcess, function( index, value ) {
                tbody.append(`<tr><td>${index}</td><td>${value}</td></tr>`)
            
            });

            paramsReportModal.find('span').html(name)
            paramsReportModal.modal('open')
               
        } 

         //extrae la informacion de visibilidad de la orden    
        var getInfoProcessVisibility = function(){

            var visibilityContent = visibilityReportModal.find("#profiles_visibility")
            visibilityContent.html('')

            visibilityReportModal.find('.progress').show()
            visibilityContent.hide()
            visibilityReportModal.modal('open')

            $.post(pathGetVisibility,function(data){
                if(data.success == true){
                    $.each(data.description, function( index, value ) { 
                
                        var active = ''
                        if(value.status == 1){
                            active = 'checked="checked"'
                        } 
                       
                        var divChek = $(`<div class="col m6">`)
                        var checkbox = $(`<p><input type="checkbox" class="filled-in profile_access" id="filled_${index}" name="_profile[]" ${active}  value="${index}"/><label for="filled_${index}">${value.profileName}</label></p>`)            

                        divChek.append(checkbox)
                        visibilityContent.append(divChek)

                    });

                    visibilityReportModal.find('.progress').hide()
                    visibilityContent.show()

                }else{
                    visibilityReportModal.modal('close')
                    Materialize.toast('No fue posible cargar la informacion',4000)
                }

            })

            $("#profile_process_order").val(id)
        } 

        var changeStatus = function(status){
            var elementValue = divProgessBar.find('.progress').children()
            elementValue.removeClass('determinate').addClass('indeterminate')
           
            $.post(pathDelete,{id:id,status:status},function(data){
                //Si es reporte se elimina el card panel
                if(status == 4){                    
                    content.fadeOut(800,function(){
                        content.remove()
                    })                
                }
            })
        }
       
        var img = ''
        switch(type){
            case 3:
            case 8:
            case 46:
                img = '/img/blue_chart.png'
                break
            case 2:
            case 12:
                img = '/img/green_chart.png'
                break
            case 4:
            case 10:
            case 21:
            case 29:
                img = '/img/yellow_chart.png'
                break
            case 20:
            case 23:
                img = '/img/red_chart.png'
                break
            case 18:
            case 22:
            case 27:
            case 32:
            case 33:
                img = '/img/purple_chart.png'
                break
            case 26:
            case 30:
            case 45:
                img = '/img/orange_chart.png'
                break
            default:
                img = '/img/grey_chart.png'

        }

        //Generacion de interfaz
        var messageStatus = 'Inicial'
        var progressRount = Math.round(progress)
        var content = $(element)

        content.addClass('process-order-panel')
        content.addClass('row')
        content.addClass('z-depth-1')
        content.attr('process-order-id',id)

        if (salient) {
            star='star'
        }

        var mainProcessOrder = $("<div class='col s12 white process-order-div'>")
        var lcontent = $('<div class="col m3 hide-on-small-only"><img class="responsive-img" src="'+img+'"></div>')
        var rcontent = $("<div class='col m9 s12'>")
      
        var divDetails = $(`<div class='col s10'>
                <h5 class='teal-text'>${name}<i  style="cursor:pointer" class="material-icons orange-text left">${star}</i></h5>
                <p>Fecha de creación: ${date}</p>
                <p>Tipo: ${typeName}</p>
                <p>${messageHomologation}</p>                
            </div>`)

        var divError = $(`<div class='col m6 s12'>Mensajes: <a href='javascript:void(0)' class='error_process'>0</a></div>`)
        var divStatus = $(`<div class='col m6 s12'> ${messageStatus} (${progressRount}%)</div>`)
        var divProgessBar = $('<div class="input-field col s12"> <div class="progress"> <div class="indeterminate" style="width: 70%"></div></div></div>')
        var divButtonHome = $('<div class="input-field col s12 right-align"><a type="button" href='+pathHomeReport+' class="btn orange"> Ver</a></div>')
                
        var divEngineMessage = $(`<span class="right"></span>`)
        divProgessBar.append(divEngineMessage)

        divError.find('.error_process').on('click',function(){
            getInfoProcessError()
        })
        //si viene en true creelos si viene en false no y color distinto

        divDetails.find('i').on('click',function(){
            getSalientSaveProcess()
        })


        mainProcessOrder.append(lcontent)
        mainProcessOrder.append(rcontent)
        rcontent.append(divDetails)
        rcontent.append(divStatus)
        rcontent.append(divError)
        
        if (errors == 0) {
            divError.hide()
        }

        rcontent.append(divProgessBar)
        rcontent.append(divButtonHome)

        divButtonHome.hide();

        content.append(mainProcessOrder)

        //private functions
        var refreshProgress = function(){
            messageStatus = ''
            switch(status) {
                case '1':
                    messageStatus = 'En proceso'
                    break;
                case '2':
                    messageStatus = 'Terminado'
                    break;
                case '3':
                    messageStatus = 'Fallido'
                    break;
                case '5':
                    messageStatus = 'Eliminando datos'
                    break;
            }

            var dprog = parseInt(progress)

            if(dprog && dprog > 0){
                var elementValue = divProgessBar.find('.progress').children()
                elementValue.removeClass('indeterminate').addClass('determinate')
                elementValue.css('width',dprog+'%')
            }

            if(status == 3 || status == 2){
                engineMessage = ''
                divProgessBar.hide()
            }

            if(engineMessage != undefined){
                divEngineMessage.html(engineMessage)
            }

            if(dprog){
                messageStatus+=' ('+dprog+'%)'
            }

            divStatus.html(messageStatus)
            divButtonHome.hide()
        }

        var drawMenu = function(){
            if(content.find('.dropdown-button').length == 0){

                var menu = $(`<a href='#' class='btn-floating dropdown-button right' data-activates='dropdown${id}'><i class='material-icons'>more_vert</i></a>`)
                rcontent.prepend(menu)

                var menuContent = $(`<ul class="dropdown-content" id="dropdown${id}">`)
                var paramsOption = $(`<li><a href="javascript:void(0)"><i class='material-icons left'>check_circle</i>Parametros</a></li>`)
                
                if (actions) {
                    
                    var visibilityOption = $(`<li><a href="javascript:void(0)"><i class='material-icons left'>visibility</i>Visibilidad</a></li>`)
                    var restartOption = $(`<li><a href="javascript:void(0)"><i class="material-icons left">cached</i>Reiniciar</a></li>`)
                    var deleteOption = $(`<li><a href="javascript:void(0)"><i class='material-icons left'>delete_forever</i>Eliminar</a></li>`)

                    deleteOption.find('a').on('click',function(){
                        changeStatus(4)
                    })

                    restartOption.find('a').on('click',function(){
                        divStatus.html('En proceso (0%)')
                        divProgessBar.show()
                        changeStatus(0)
                    })

                    visibilityOption.find('a').on('click',function(){
                        getInfoProcessVisibility()
                    })

                    menuContent.append(deleteOption)
                    if(messageHomologation == ''){
                        menuContent.append(restartOption)
                    }
                    menuContent.append(visibilityOption)
                
                }
                
                paramsOption.find('a').on('click',function(){
                    getInfoProcessParams()
                })

                menuContent.append(paramsOption)
                content.prepend(menuContent)

                setTimeout(function(){//extraña razon
                    menu.dropdown({
                            inDuration: 300,
                            outDuration: 225,
                            constrain_width: false, // Does not change width of dropdown to that of the activator
                            hover: false, // Activate on hover
                            gutter: 0, // Spacing from edge
                            belowOrigin: true // Displays dropdown below the button
                    })
                },1000)

            }
        }

        var showErrors = function(){
            if(parseInt(errors) > 0){
                divError.find('.error_process').html(`${errors}`)
                divError.show()
            }
        }

        var drawData = function(){
            if(status == 2 || status == 3){
                drawMenu()
            }

            refreshProgress()
            showErrors()

            if (status == 2) {
                divButtonHome.show()
            }
        }

        drawData()//ejecucion de inicializacion

        //public functions

        this.refreshData = function(data){
            progress = data.progress
            errors = data.errors
            status = data.status
            engineMessage = data.message

            drawData()
        }

    }

    //front object managment extention
    $.fn.reportOrder = function(params){
        return this.each(function(){
            var element = $(this);
            if (element.data('reportOrder')){
                var reportOrder = element.data('reportOrder');
            }else{
                var reportOrder = new ReportOrder(this,params);
                element.data('reportOrder', reportOrder);
            }
            return reportOrder;
        });
    }

})(jQuery)