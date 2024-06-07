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

    # Pega o parâmetro do ano
    $parametroAno = post('parametroAno', date('Y'));

    ######

    $relatorio = new Relatorio();

    $select = "SELECT tbpessoa.nome,                      
                      tbservidor.idservidor,
                      tbservidor.idservidor,
                      tbservidor.dtAdmissao
                 FROM tbservidor JOIN tbpessoa USING (idpessoa)
                WHERE tbservidor.situacao = 1
                  AND idPerfil = 1
                  AND year(dtAdmissao) = '{$parametroAno}'
             ORDER BY tbpessoa.nome";

    $result = $servidor->select($select);

    $relatorio->set_titulo('Relatório de Servidores Estatutários Ativos');
    $relatorio->set_subtitulo("Admitidos em {$parametroAno}");
    $relatorio->set_label(['Servidor', 'Cargo', 'Email Uenf', 'Admissão',]);
    $relatorio->set_align(["left", "left", "left"]);
    $relatorio->set_funcao([null, null, null, "date_to_php"]);
    $relatorio->set_classe([null, "pessoal", "pessoal"]);
    $relatorio->set_metodo([null, "get_cargoSimples", "get_emailUenf"]);
    $relatorio->set_conteudo($result);

    # Cria um array com os anos possíveis
    $anoInicial = 1993;
    $anoAtual = date('Y');
    $anoExercicio = arrayPreenche($anoInicial, $anoAtual, "d");

    $relatorio->set_formCampos(array(
        array('nome' => 'parametroAno',
            'label' => 'Ano de Admissão:',
            'tipo' => 'combo',
            'size' => 10,
            'padrao' => $parametroAno,
            'array' => $anoExercicio,
            'title' => 'Ano',
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 1)));

    $relatorio->set_formFocus('cargo');
    

    $relatorio->show();

    $page->terminaPagina();
}