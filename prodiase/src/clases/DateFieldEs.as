package clases
{
	import mx.controls.DateField;
	import mx.utils.StringUtil;

	public class DateFieldEs extends DateField
	{
		public function DateFieldEs()
		{
			super();
			formatString = "DD/MM/YYYY";
			dayNames = [ 'D', 'L', 'M', 'M', 'J', 'V', 'S' ];
			monthNames = [ 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre' ];
		}
		
		public function get fechaSeleccionada(): String {
			if (this.selectedDate==null) return null;
			else {
				var dia : String = String(this.selectedDate.getDate()); 
				var mes : String = String(this.selectedDate.getMonth() + 1);
				var ano : String = String(this.selectedDate.getFullYear());
				if (dia.length==1) dia = "0" + dia;
				if (mes.length==1) mes = "0" + mes;
			    return ano + "-" + mes + "-" + dia;
			}
		}

		public function set fechaSeleccionada(value:String):void {
			value = mx.utils.StringUtil.trim(value);
			if (value=="") {
				this.selectedDate = null;
			} else {
				value = value.split(" ")[0];
				var dia : Number = Number(value.substr(8, 2));
				var mes : Number = Number(value.substr(5, 2)) - 1;
				var ano : Number = Number(value.substr(0, 4));
				this.selectedDate = new Date(ano, mes, dia);
			}
		}
	}
}
