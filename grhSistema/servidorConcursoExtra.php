<?php

/*
 * Rotina Extra de Validação
 * 
 */

# Se o concurso não for o de 2001
if($campoValor[0] <> 2){
    # Apaga a instituição
    # Só vale para o concurso de 2001
    $campoValor[5] = null;    
}