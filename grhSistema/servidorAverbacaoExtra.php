<?php

/*
 * Rotina Extra de Validação
 * 
 */


/*
 * Verifica se data é anterior a admissão
 */

$dtInicial = $campoValor[4];
$dtFinal = $campoValor[5];
$idServidor = $campoValor[10];

$pessoal = new Pessoal();
$dtAdmissao = date_to_bd($pessoal->get_dtAdmissao($idServidor));

# Verifica se a data inicial é posterior a data de admissão
if ($dtInicial >= $dtAdmissao) {
    $msgErro .= 'Você não pode ter tempo cadastrado após a admissão!\nA data inicial está errada!';
    $erro = 1;
}

# Verifica se a data de término é posterior a de admissao
if ($dtFinal >= $dtAdmissao) {
    $msgErro .= 'Você não pode ter tempo cadastrado concomitante ao tempo de Uenf!\nA data final está errada!';
    $erro = 1;
}

# Verifica se a data de término é posterior a data inicial
if ($dtFinal < $dtInicial) {
    $msgErro .= 'Você não pode ter a data final anterior a data inicial!';
    $erro = 1;
}
