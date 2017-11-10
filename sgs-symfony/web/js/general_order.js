(function($){

    $('body').append(`<!-- Modal Structure -->
        <div id="errors_report" class="modal modal-fixed-footer">
            <div class="modal-content">
                <h4 class="teal-text">Listado de incidencias</h4>
                <table class="table" id="process_error_table">
                    <thead>
                        <tr>
                            <td>Fecha</td>
                            <td>Id del proceso</td>
                            <td>Error</td>
                            <td>Severidad</td>
                        </tr>       
                    </thead>
                    <tbody>
                   
                        
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <a href="#!" class="modal-action modal-close waves-effect waves-green btn-flat ">Cerrar</a>
                <a class="btn" id="btn_dowland_report_error">Descargar Reporte</a>
            </div>
        </div>`)

    //object constructor
    var GeneralOrder = function(element,params){
    
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
        var pathVisibilitySave = params.pathVisibilitySave
        var type= params.type

        var engineMessage = ''
    
        //extrae la informacion de los errores y los pinta en una ventana modal    
        var getInfoProcessError = function(path){
            var tbody = $("#process_error_table").find("tbody")
            tbody.html("")

            $.post(path,function(data){
                if(data.success == true){
                    tbody.html(data.description.html)
                    $("#btn_dowland_report_error").attr('href',data.description.pathDowland);
                    openErrorsReportModal()
                }
            })
        }

        //abre la modal que contiene los errores
        var  openErrorsReportModal = function(){
            $('#errors_report').openModal({
                ready: function(){
                    clearInputs()
                },

                complete: closeErrorsReportModal
            });
        }
        
        //cierra la modal que contiene los herrores
        var closeErrorsReportModal = function() {
            $("#errors_report").closeModal()           
        }

        var changeStatus = function(status){
            var elementValue = divProgessBar.find('.progress').children()
            elementValue.removeClass('determinate').addClass('indeterminate')
           
            $.post(pathDelete,{id:id,status:status},function(data){
                if(status == 4){
                    content.fadeOut(800,function(){
                        content.remove()
                    })
                }
            })
        }

        //Generacion de interfaz
      
        var content = $(element)

        content.addClass('process-order-panel')
        content.addClass('row')
        content.addClass('z-depth-1')
        content.attr('process-order-id',id)

        var mainProcessOrder = $("<div class='col s12 white process-order-div'>")
        
        var rcontent = $(`<div class='col s12'>`)
      
        var divDetails = $(`<div class='col s10'>
                <h5 class='teal-text'>${name}</h5>
                <p>Fecha de creación: ${date}</p>
                <p>Incidencias: <a href='javascript:void(0)' class='error_process'>0</a></p>
            </div>`)

        
        var divProgessBar = $('<div class="input-field col s12"> <div class="progress"> <div class="indeterminate" style="width: 70%"></div></div></div>')
                
        var divEngineMessage = $(`<span class="right"></span>`)
        divProgessBar.append(divEngineMessage)

        divDetails.find('.error_process').on('click',function(){
            getInfoProcessError(path)
        })
        //si viene en true creelos si viene en false no y color distinto

        mainProcessOrder.append(rcontent)
        rcontent.append(divDetails)
        rcontent.append(divProgessBar)

        content.append(mainProcessOrder)

        //private functions
        var refreshProgress = function(){
            var dprog = parseInt(progress)
            if(status == 3 || status == 2){
                dprog = 100
                engineMessage = ''
            }

            if(dprog > 0){
                var elementValue = divProgessBar.find('.progress').children()
                elementValue.removeClass('indeterminate').addClass('determinate')
                elementValue.css('width',dprog+'%')
            }

            if(engineMessage != undefined){
                divEngineMessage.html(engineMessage)
            }
        }

        var drawMenu = function(){
            if(content.find('.dropdown-button').length == 0){

                var menu = $(`<a href='#' class='btn-floating dropdown-button right' data-activates='dropdown${id}'><i class='material-icons'>more_vert</i></a>`)
                rcontent.prepend(menu)

                var menuContent = $(`<ul class="dropdown-content" id="dropdown${id}">`)
                
                if (actions) {
                    
                    var restartOption = $(`<li><a href="javascript:void(0)"><i class="material-icons left">cached</i>Reiniciar</a></li>`)

                    restartOption.find('a').on('click',function(){
                        changeStatus(0)
                    })

                    menuContent.append(restartOption)
                
                }

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
                divDetails.find('.error_process').html(`${errors}`)
            }
        }

        var drawData = function(){
            if(status == 2 || status == 3){
                drawMenu()
            }

            if (status == 2 && type != 100) {
                divButtonHome.show()
            }

            refreshProgress()
            showErrors()
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
    $.fn.generalOrder = function(params){
        return this.each(function(){
            var element = $(this);
            if (element.data('generalOrder')){
                var generalOrder = element.data('generalOrder');
            }else{
                var generalOrder = new GeneralOrder(this,params);
                element.data('generalOrder', generalOrder);
            }
            return generalOrder;
        });
    }

})(jQuery)