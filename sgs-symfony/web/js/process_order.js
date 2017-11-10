(function($){

    var errorProcessModal = $(`
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
                    <tbody></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <a class="btn" id="btn_dowland_report_error">Descargar Reporte</a>
            </div>
        </div>`)

    var paramsProcessModal = $(`
        <div class="modal">
            <div class="modal-content">
                <h4 class="teal-text">Listado de Parametros <span></span></h4>
                <table class="table" id="process_params_table">
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

    $('body').append(errorProcessModal)
    $('body').append(paramsProcessModal)

    errorProcessModal.modal({
        ready: clearInputs,
    })

    paramsProcessModal.modal({
        ready: clearInputs,
    })

    //object constructor
    var ProcessOrder = function(element,params){
    
        //private atributes
        var name = params.name
        var date = params.date
        var status = params.status
        var progress = params.progress
        var id = params.id
        var errors = params.errors
        var path = params.path
        var type = params.type
        var changeStatusPath = params.pathDelete
        var paramsProcess = params.params
        var actions = params.actions
        var engineMessage = ''
        var autor = params.autor
        var previusStatus = params.status
        
        //extrae la informacion de los errores y los pinta en una ventana modal    
        var getInfoProcessError = function(path){
            var tbody = errorProcessModal.find("tbody")
            tbody.html("")

            errorProcessModal.find('table').hide()
            errorProcessModal.find('.progress').show()
            errorProcessModal.modal('open')

            $.post(path,function(data){
                if(data.success == true){
                    tbody.html(data.description.html)
                    errorProcessModal.find('a').attr('href',data.description.pathDowland);
                }

                errorProcessModal.find('.progress').hide()
                errorProcessModal.find('table').show()
            })
        } 

        //extrae la informacion de los errores y los pinta en una ventana modal    
        var getInfoProcessParams = function(paramsProcess){
            var tbody = paramsProcessModal.find("tbody")
            tbody.html("")

            $.each(paramsProcess, function( index, value ) {
                tbody.append(`<tr><td>${index}</td><td>${value}</td></tr>`)
            
            });

            paramsProcessModal.find('span').html(name)

            paramsProcessModal.modal('open')
               
        } 

        var changeStatus = function(status){
            var elementValue = divProgessBar.find('.progress').children()
            elementValue.removeClass('determinate').addClass('indeterminate')
            divProgessBar.show()
            previusStatus = status

            $.post(changeStatusPath,{id:id,status:status},function(data){              
                //algo con la respuesta
            })
        }

        //Generacion de interfaz
        var messageStatus = 'Inicial'
        
        var progressRount = Math.round(progress)
      
        var content = $(element)

        content.addClass('process-order-panel')
        content.addClass('row')
        content.addClass('z-depth-1')
        content.attr('process-order-id',id)

        var mainProcessOrder = $("<div class='col s12 white process-order-div'>")
        var lcontent = $("<div class='col m3 hide-on-small-only'><img class='responsive-img' src='/img/green_gear.png'></div>")
        var rcontent = $("<div class='col m9 s12'>")

        var divDetail = $(`<div class='col s10'>
                <h5 class='teal-text'>${name}</h5>
                <p>Fecha de creación: ${date}</p>
                <p>Autor: ${autor}</p>
            </div>`)
        var divError = $(`<div class='col m6 s12'>Mensajes: <a href='javascript:void(0)' class='error_process'>0</a></div>`)
        var divStatus = $(`<div class='col m6 s12'> ${messageStatus} (${progressRount}%)</div>`)
        var divProgessBar = $(`<div class="input-field col s12"> 
            <div class="progress"><div class="indeterminate" style="width: 70%"></div></div>
            </div>`)

        var divEngineMessage = $(`<span class="right"></span>`)

        divProgessBar.append(divEngineMessage)

        divError.find('.error_process').on('click',function(){
            getInfoProcessError(path)
        })

        mainProcessOrder.append(lcontent)
        mainProcessOrder.append(rcontent)

        rcontent.append(divDetail)
        rcontent.append(divStatus)
        rcontent.append(divError)
        
        if (errors == 0) {
            divError.hide()
        }

        rcontent.append(divProgessBar)

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
                divProgessBar.show()
            }

            if(status == 3 || status == 2){
                dprog = 100
                engineMessage = ''
                divProgessBar.hide()
            }
    
            if(engineMessage != undefined){
                divEngineMessage.html(engineMessage)
            }

            if(dprog){
                messageStatus += ' ('+dprog+'%)'                
            }

            divStatus.html(messageStatus)
        }

        var drawMenu = function(){
            if(content.find('.dropdown-button').length == 0){

                var menu = $(`<a href='#' class='btn-floating dropdown-button right' data-activates='dropdown${id}'><i class='material-icons'>more_vert</i></a>`)
                rcontent.prepend(menu)

                var menuContent = $(`<ul class="dropdown-content" id="dropdown${id}">`)
                if (actions) {
                    var restartOption = $(`<li><a href="javascript:void(0)"><i class="material-icons left">cached</i>Reiniciar</a></li>`)
                    if (type != 31) {                        
                        var deleteOption = $(`<li><a href="javascript:void(0)"><i class='material-icons left'>delete_forever</i>Eliminar</a></li>`)

                        deleteOption.find('a').on('click',function(){
                            changeStatus(4)
                        })
                        menuContent.append(deleteOption)
                    }

                    restartOption.find('a').on('click',function(){
                        divStatus.html('En proceso (0%)')
                        divProgessBar.show()
                        changeStatus(0)
                    })


                    menuContent.append(restartOption)
                
                }
                var paramsOption = $(`<li><a href="javascript:void(0)"><i class='material-icons left'>check_circle</i>Parametros</a></li>`)

                paramsOption.find('a').on('click',function(){
                    getInfoProcessParams(paramsProcess)
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

            if(status == 4 && previusStatus == 4){
                setTimeout(function(){
                    content.fadeOut(800,function(){
                        content.remove()
                    })
                },1000)
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
    $.fn.processOrder = function(params){
        return this.each(function(){
            var element = $(this);
            if (element.data('processOrder')){
                var processOrder = element.data('processOrder');
            }else{
                var processOrder = new ProcessOrder(this,params);
                element.data('processOrder', processOrder);
            }
            return processOrder;
        });
    }

})(jQuery)