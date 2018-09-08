// ActionScript file
	import clases.HTTPServices;
	
	import flash.events.Event;
	import flash.utils.Timer;
	
	import mx.controls.Alert;
	import mx.events.CloseEvent;
	
	include "../control_acceso.as";
	
	
	[Bindable] private var httpPacientes : HTTPServices = new HTTPServices;
	
	private var timerActColas : Timer;
	private var _idIngresoMovimiento : String;
	
	public function get idIngresoMovimiento():String{
		return _idIngresoMovimiento;
	}
		
	public function fncDetenerTimer():void { timerActColas.stop(); }
	public function fncIniciarTimer():void { timerActColas.start(); }
	
	//inicializa las variales necesarias para el modulo
	public function fncInit(e:Event):void
	{
		//Timer para Actualizar las Colas
		timerActColas = new Timer(300000);
		timerActColas.addEventListener(TimerEvent.TIMER, fncTraerColas);
		timerActColas.start();
		//http utilizado para traer los pacientes en la cola de espera
		httpPacientes.url = "cola_internacion/cola_internacion.php";
		httpPacientes.addEventListener("acceso",acceso);
		httpPacientes.send({rutina:"lista_pacientes"});
		//filtro de busqueda de pacientes en cola de espera
		txtNombrePaciente.addEventListener("change",fncFiltrarPacientes);
	}					
	
	//filtra la cola de espera
	private function fncFiltrarPacientes(e:Event):void
	{
			if (!(txtNombrePaciente.length % 2)){
				httpPacientes.send({rutina:"lista_pacientes",filtro_pacientes:txtNombrePaciente.text});
			}
	}
	
	//actualiza las dos colas de espera
	private function fncTraerColas(e:Event):void
	{
		httpPacientes.send({rutina:"lista_pacientes"});
	}
	
	public function fncActColas():void
	{
		httpPacientes.send({rutina:"lista_pacientes"});
	}
	
	//envia el evento atender paciente al modulo padre
	public function fncVer():void
	{		
		_idIngresoMovimiento = gridPacientes.selectedItem.@id_ingreso_movimiento;
		timerActColas.stop();
		dispatchEvent(new Event("EventFicha"));
	}
	

	// envia el evento para que el modulo padre se encarde de dar el egreso
	public function fncEgreso():void
	{		
		_idIngresoMovimiento = gridPacientes.selectedItem.@id_ingreso_movimiento;
		timerActColas.stop();
		dispatchEvent(new Event("EventEgreso"));
	}
	
	// envia el evento para que el modulo padre se encarde de dar el pase a otro servicio
	public function fncPase():void
	{		
		_idIngresoMovimiento = gridPacientes.selectedItem.@id_ingreso_movimiento;		
		timerActColas.stop();
		dispatchEvent(new Event("EventPase"));
	}