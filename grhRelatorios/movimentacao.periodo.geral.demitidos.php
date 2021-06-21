<?php

/**
 * Relatório
 *    
 * By Alat
 */
# Servidor logado 
$idUsuario = null;

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();
    
    # Título do Relatório
    $titulo = "Relatório Anual de Servidores<br/>";
    
    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $de = post('de', date('Y'));
    $para = post('para', date('Y'));
    $motivo = post('motivo', '*');
    $perfil = post('perfil', '*');

    ###### Relatório 1

    $select = 'SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbdocumentacao.cpf,
                      tbpessoa.dtNasc,
                      tbservidor.idServidor,
                      tbservidor.idServidor,
                      tbperfil.nome,
                      tbservidor.dtAdmissao,
                      tbservidor.dtDemissao,
                      tbservidor.dtPublicExo,
                      tbmotivo.motivo
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)                                
                                 LEFT JOIN tbperfil ON(tbservidor.idPerfil = tbperfil.idPerfil)
                                 LEFT JOIN tbdocumentacao ON (tbpessoa.idPessoa = tbdocumentacao.idPessoa)
                                 LEFT JOIN tbmotivo ON (tbservidor.motivo = tbmotivo.idMotivo)
                WHERE YEAR(tbservidor.dtDemissao) >= "' . $de . '"
                  AND YEAR(tbservidor.dtDemissao) <= "' . $para . '"';

    # Motivo
    if ($motivo <> "*") {
        $select .= ' AND idMotivo =  ' . $motivo;
        $titulo .= $servidor->get_motivoAposentadoria($motivo);
    } else {
        $titulo .= "Exonerados, Aposentados e Demitidos";
    }
    
    # Perfil
    if ($perfil <> "*") {
        $select .= ' AND tbservidor.idPerfil =  ' . $perfil;
        $titulo .= '<br/>'.$servidor->get_perfilNome($perfil);
    }
    
    $select .= ' ORDER BY tbservidor.dtDemissao';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    if ($de == $para) {
        $relatorio->set_titulo("{$titulo}<br/>{$de}");
    } else {
        $relatorio->set_titulo("{$titulo}<br/>{$de} a {$para}");
    }
    $relatorio->set_subtitulo('Ordenado pela Data de Saída');

    $relatorio->set_label(array('IdFuncional', 'Nome', 'CPF', 'Nascimento', 'Cargo', 'Lotação', 'Perfil', 'Admissão', 'Saída', 'Publicação', 'Motivo'));
    $relatorio->set_align(array('center', 'left', 'center', 'center', 'left', 'left', 'center', 'center', 'center', 'center', 'left'));
    $relatorio->set_funcao(array(null, null, null, "date_to_php", null, null, null, "date_to_php", "date_to_php", "date_to_php"));

    $relatorio->set_classe(array(null, null, null, null, "pessoal", "pessoal"));
    $relatorio->set_metodo(array(null, null, null, null, "get_cargo", "get_lotacao"));

    $relatorio->set_conteudo($result);

    $listaMotivo = $servidor->select("SELECT idmotivo, motivo
                                         FROM tbmotivo ORDER BY motivo");
    array_unshift($listaMotivo, array('*', "-- Todos --"));

    $listaPerfil = $servidor->select("SELECT idPerfil, nome
                                         FROM tbperfil ORDER BY nome");
    array_unshift($listaPerfil, array('*', "-- Todos --"));

    $relatorio->set_formCampos(array(
        array('nome' => 'de',
            'label' => 'De:',
            'tipo' => 'texto',
            'size' => 4,
            'title' => 'de',
            'onChange' => 'formPadrao.submit();',
            'padrao' => $de,
            'col' => 2,
            'linha' => 1),
        array('nome' => 'para',
            'label' => 'Para:',
            'tipo' => 'texto',
            'size' => 4,
            'title' => 'Para',
            'onChange' => 'formPadrao.submit();',
            'padrao' => $para,
            'col' => 2,
            'linha' => 1),
        array('nome' => 'motivo',
            'label' => 'Motivo:',
            'tipo' => 'combo',
            'array' => $listaMotivo,
            'size' => 15,
            'padrao' => $motivo,
            'title' => 'Motivo da Saída',
            'col' => 4,
            'onChange' => 'formPadrao.submit();',
            'linha' => 1),
        array('nome' => 'perfil',
            'label' => 'Perfil:',
            'tipo' => 'combo',
            'array' => $listaPerfil,
            'size' => 15,
            'padrao' => $perfil,
            'title' => 'Perfil do servidor',
            'col' => 4,
            'onChange' => 'formPadrao.submit();',
            'linha' => 1)));

    $relatorio->set_formFocus('ano');
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}