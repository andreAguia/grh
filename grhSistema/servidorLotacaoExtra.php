<?php

/*
 * Rotina Extra de Validação
 * 
 */

$dtInicial = $campoValor[0];
$idServidor = $campoValor[4];

$pessoal = new Pessoal();
$dtAdmissao = date_to_bd($pessoal->get_dtAdmissao($idServidor));

# Verifica se a data de lotação é anterior a de admissão
if(($dtInicial < $dtAdmissao) AND (!is_null($dtInicial))){
    $msgErro.='Você não pode ser lotado antes de ser admitido!\nA data está errada!\n';
    $erro = 1;
}

# Verifica se tem a data Inicial já consta em alguma lotação anterior
# Para não ficar com 2 lotações iniciando na mesma data o que provoca duplicidade de servidor.
if($pessoal->temLotacaoNestaData($dtInicial, $idServidor, $id)){
    $msgErro.='Este servidor já tem uma lotação nesta data!\nEle não pode ser lotado em mais de um local no mesmo dia!\n';
    $erro = 1;
}
