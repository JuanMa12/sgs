(function($){
    //object constructor
    var Wizard = function(element,params){
        var modal = $(element)

        var parameters = {}
        for(var param of params){
            parameters[param.typeOrder] = param
        }
       
        modal.addClass('modal')
        modal.addClass('modal-fixed-footer')

        modal.html(`
            <div class="modal-content"></div>
            <div class="modal-footer">
                <a id="report_button" style="display:none" class="btn orange">Finalizar</a>
                <a class="btn" id="btn_next" >Siguiente</a>
                <a class="btn-flat" id="btn_after" style="display:none">Atras</a>
            </div>`)

        var content = modal.find('.modal-content')

        var forms = {}
        var typeIdSelect
        var currentSteep = 0
        var currentForm = 0
        
        ///construccion de primer paso
        var fstep = $(`
            <div step="0" class='steep_body row'>
                <h4 class="orange-text">Tipo de Reporte</h4>
            </div>
            `)
       
        var lcontent = $("<div class='col m6 s12'  style='height:310px; overflow-y: auto;'>")
        var rcontent = $("<div class='col m6 s12 hide-on-small-only'>")
        fstep.append(lcontent)
        fstep.append(rcontent)

        for(let type of params){
            var divInfoType=$(`<p>
                <input 
                    name="_type" 
                    type="radio" 
                    class="report_type" 
                    value="${type.typeOrder}"
                    id="type${type.typeOrder}"/>

                <label for="type${type.typeOrder}">${type.name}</label>
            </p>`)
            var divDescriptionType = $(`<p id="description${type.typeOrder}" 
                        class="description_type" style="display:none">${type.description}</p>`)
             
            lcontent.append(divInfoType)
            rcontent.append(divDescriptionType)
            
            divInfoType.on('change',function(){
                $(".description_type").hide()
                typeIdSelect = $(".report_type:checked").val()
                $("#description"+typeIdSelect).show()
                resetForm()                  
                currentForm = 0

            })
        }
        content.append(fstep)
        //termino de contruir el paso 0

        
        //construccion de paso 1
        var step1 = $(`<div step="1" class="row steep_body">`)
        var lcontent1 = $('<div class="col m2 hide-on-small-only center-align" style="position:fixed">')
        var rcontent1 = $('<div class="col s12 m10 offset-m2">')

        step1.append(lcontent1)
        step1.append(rcontent1)

        content.append(step1)
        //cunstruccion paso final

        var step2 = $('<div step="2">')
        step2.addClass('steep_body')
        var content2 = $('<div class="col s12"><h4 class="teal-text">Finalizar</h4>')
        var endMessage = $(`<p>Por favor diligenciar el campo nombre y porteriormente pulsar en el botón de finalizar para 
                            generar el reporte con los parametros dados. A continuacion se generara el procesos
                            de consolidación de datos.</p>`)
        var nameReportForm = $(`<form id="name_report_form"><div class="input-field">
                            <input type="text" 
                                id="_name"
                                name="_name"
                                required>
                            <label for="_name">Nombre del reporte</label>
                        </div></form>`)
        
        content2.append(endMessage)
        content2.append(nameReportForm)
        step2.append(content2)
        
        content.append(step2)        

        changeSteep(0) 

        //se inicializa la modal
        modal.modal({
            ready: function(){
                clearInputs()
                changeSteep(0)
            },
            complete:function(){
              resetForm()  
            }
        });

        var iconHome  = $('<a class="btn-floating orange" style="margin-bottom: 10px;"><i class="material-icons">home</i></a>')

        $("#btn_next").on('click',function(){
            
            if(currentSteep == 0){
                if (typeIdSelect) {                    
                    lcontent1.html('')

                    iconHome.on('click',function(){
                        resetForm()
                        changeSteep(0)
                    })
                    
                    lcontent1.append(iconHome)

                    for(let number of Object.keys(parameters[typeIdSelect].params)){ 
                        let icon = 'account_balance'
                        if (parameters[typeIdSelect].params[number].icon) {
                            icon = parameters[typeIdSelect].params[number].icon
                        }
 
                        var divStep = $(`
                            <div class="center-align steps" id="num${number}">
                                <a href="#!"><i class="material-icons grey-text">${icon}</i></a>
                            </div>`)

                        divStep.on('click',function(){
                            changeForm(number)
                        })

                        lcontent1.append(divStep)
                    }
                    
                    changeSteep(Number(currentSteep) + 1)
                    changeForm(0) 

                }else{
                    Materialize.toast('Debe seleccionar al menos un tipo de reporte para continuar',4000)
                }
            
            }else if(currentSteep == 1 && currentForm < (parameters[typeIdSelect].params.length - 1)) {           
                
                changeForm(Number(currentForm) + 1)

            }else{//lleva a la pantalla de finalizar
                let mode = ''
                if(parameters[typeIdSelect].params[currentForm].mode){
                    mode = parameters[typeIdSelect].params[currentForm].mode
                }

                var validate = $("#"+parameters[typeIdSelect].params[currentForm].name+mode).valid()
                if (validate) {
                    changeSteep(currentSteep + 1)
                    changeColor(currentForm + 1)
                }
            }

        })

        $("#btn_after").on('click',function(){
            if (currentSteep == 1 && currentForm != 0) {
                changeForm(currentForm - 1) 
                
            }else{
                changeSteep(currentSteep - 1)
            }
        })

        $("#report_button").on('click',function(){
            //recopilacion de informacion de todos los formularios
            let arrData = [`type_id=${typeIdSelect}`]
            for(let form of parameters[typeIdSelect].params){
                let mode = ''
                if(form.mode){
                    mode = form.mode
                }

                arrData.push($(`#${form.name}${mode}`).serialize())
            }
            arrData.push($(`#name_report_form`).serialize())
            let strData = arrData.join("&")
            
            $.post(parameters[typeIdSelect].savePath,strData,function(data){
                
                if(data.success == true){
                    addNewElement(data.description)
                    resetForm()
                    modal.modal('close')
                }else{
                    Materialize.toast(data.description,4000)
                }

            })

        })

        function changeSteep(step){
            $("#btn_after").hide()
            $("#report_button").hide()
            $("#btn_next").show()

            if (step > 0) {
                $("#btn_after").show()
            }

            if (step == 2) {
                $("#btn_next").hide()
                $("#report_button").show()
                
            }
            currentSteep = step
            $(".steep_body").hide()
            $(".steep_body[step='"+currentSteep+"']").show()
        }

        function changeForm(tagetForm){

            var objType = parameters[$(".report_type:checked").val()]
            let mode = ''
            let currentMode = ''
            
            if(objType.params[tagetForm].mode){
                mode = objType.params[tagetForm].mode
            }

            if(objType.params[currentForm].mode){
                currentMode = objType.params[currentForm].mode
            }

            //validacion  del formulario
            if (tagetForm > 0 && !$("#"+objType.params[currentForm].name+currentMode).valid()) {
                return
            }   

            rcontent1.find('.info_params').hide()

            if (!forms[objType.params[tagetForm].name+mode]) {//carga de formulario desde el servidor
                
                let postParams = {_name:objType.params[tagetForm].name}

                if (mode != '') {
                    postParams._mode = mode
                }

                $.post(objType.htmlParams,postParams,function(data){
                    if(data.success == true){
                        forms[objType.params[tagetForm].name+mode] = data.description
                        rcontent1.append(data.description)
                    }

                })

            }else{
                $("#"+objType.params[tagetForm].name+mode+"").show()
            }

            currentForm = tagetForm
            changeColor(tagetForm)

        }

        function changeColor(item){
            $(".steps").find("i.cactive").remove()
            $(`#num${item}`).append(`
                <i class="material-icons cactive">keyboard_arrow_right</i>
                `)

            
            $(`#num${item}`).find('i').removeClass('grey-text')
            $(`#num${item}`).find('i').addClass('teal-text')
        }

        function resetForm(){
            $("#_name").val('')
            
            for(let form of parameters[typeIdSelect].params){
                let mode = ''
                if(form.mode){
                    mode = form.mode
                }
                if ($(`#${form.name}${mode}`).length >0) {
                    document.getElementById(form.name+mode).reset(); 
                }
            }  
        }


        this.openModal = function(){
            modal.modal('open')
        }

        this.closeModal = function(){
            modal.modal('close')
        }

        function addNewElement(data){
            var element = $("<div>")
            element.reportOrder(data)
            $("#report_content").prepend(element)    
        }  

    }

    //front object managment extention
    $.fn.wizard = function(params){
        return this.each(function(){
            var element = $(this);
            if (element.data('wizard')){
                var wizard = element.data('wizard');
            }else{
                var wizard = new Wizard(this,params);
                element.data('wizard', wizard);
            }
            return wizard;
        });
    }

})(jQuery)