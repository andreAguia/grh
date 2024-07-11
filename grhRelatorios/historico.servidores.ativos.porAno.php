<?php

/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */
# Servidor logado 
$idUsuario = null;

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros
    $cargo = post('cargo', "Adm/Tec");
    $parametroAno = post('parametroAno', date('Y'));

    ######

    $select = 'SELECT tbservidor.idfuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     dtAdmissao,
                     dtDemissao
                FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                JOIN tbdocumentacao USING (idPessoa)
                                JOIN tbcargo USING (idCargo)
                                JOIN tbtipocargo USING (idTipoCargo)
               WHERE year(dtAdmissao) <= "' . $parametroAno . '"
                 AND (dtDemissao IS null OR year(dtDemissao) >= "' . $parametroAno . '")
                 AND tbtipocargo.tipo = "' . $cargo . '"  
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Histórico de Servidores');
    $relatorio->set_tituloLinha2("{$cargo}<br/>Ativos em  {$parametroAno}");
    $relatorio->set_subtitulo('Ordenados pelo Nome');
    $relatorio->set_label(['IdFuncional', 'Nome', 'Cargo', 'Perfil', 'Admissão', 'Saída']);
    $relatorio->set_align(["center", "left", "left"]);
    $relatorio->set_funcao([null, null, null, null, "date_to_php", "date_to_php"]);

    $relatorio->set_classe([null, null, "pessoal", "pessoal"]);
    $relatorio->set_metodo([null, null, "get_cargoSimples", "get_perfil"]);

    $relatorio->set_conteudo($result);

    # Seleciona o tipo de cargo
    $listaCargo = $servidor->select('SELECT distinct tipo,tipo from tbtipocargo');

    # Cria um array com os anos possíveis
    $anoInicial = 1993;
    $anoAtual = date('Y');
    $anoExercicio = arrayPreenche($anoInicial, $anoAtual, "d");

    $relatorio->set_formCampos(array(
        array('nome' => 'cargo',
            'label' => 'Tipo de Cargo:',
            'tipo' => 'combo',
            'array' => $listaCargo,
            'size' => 20,
            'col' => 3,
            'padrao' => $cargo,
            'onChange' => 'formPadrao.submit();',
            'linha' => 1),
        array('nome' => 'parametroAno',
            'label' => 'Ano:',
            'tipo' => 'combo',
            'size' => 10,
            'padrao' => $parametroAno,
            'array' => $anoExercicio,
            'title' => 'Ano',
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 1)));
    
    $relatorio->show();
    $page->terminaPagina();
}