import '../../css/form/recipe_fabrication.scss';
import Swal from 'sweetalert2'


$(".submit").on('click', function(e){
    e.preventDefault();
    let $t= $(this);
    if($t.val() === "start"){
        $t.closest('form').submit();
        return true;
    }else if($t.val() === "stop"){
        Swal.fire({
            title: $t.data('title'),
            input: 'text',
            inputValue: $t.data('hours')
        }).then((result)=>{
            if(result.value){
                $("#recipe_fabrication_hours").val(result.value);
                $("#recipe_fabrication_end").val(true);
                $t.closest('form').submit();
                return true;
            }else{
            return false;
            }

        })
    }else {
        return false;
    }
});



