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
    
    # Pega os parâmetros dos relatórios
    $cidade = get('cidade', post('cidade'));
    if ($cidade == "*") {
        $cidade = null;
    }
    $subTitulo = null;


    ######

    $select = "SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     CONCAT(tbestado.uf,' - ',tbcidade.nome)
                FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     JOIN tbcidade USING (idCidade)
                                     JOIN tbestado USING (idEstado)
                                     JOIN tbperfil USING (idPerfil)
               WHERE tbservidor.situacao = 1
                 AND tbperfil.tipo <> 'Outros'";
    
     if (!is_null($cidade)) {
         $select .= " AND idCidade = {$cidade}";
     }
     
     $select .= " ORDER BY tbestado.uf,tbcidade.nome,tbpessoa.nome";

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores Ativos Com Endereço');
    $relatorio->set_subtitulo('Agrupado por Cidade e Ordenado pelo nome');
    $relatorio->set_label(['IdFuncional', 'Nome', 'Telefones', 'Endereço', 'Cidade']);
    $relatorio->set_align(["center", "left", "left", "left", "left"]);
    $relatorio->set_width([15,20,20,30]);
    $relatorio->set_classe([null, null,  "pessoal",  "pessoal"]);
    $relatorio->set_metodo([null, null,  "get_telefones",  "get_endereco"]);

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(4);
    
    
    $relatorio->set_bordaInterna(true);
    $listaCidade = $servidor->select('SELECT DISTINCT idCidade, CONCAT(tbestado.uf," - ",tbcidade.nome) as cidade
                                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                                              JOIN tbcidade USING (idCidade)
                                                              JOIN tbestado USING (idEstado)
                                                              JOIN tbperfil USING (idPerfil)
                                         WHERE tbservidor.situacao = 1
                                           AND tbperfil.tipo <> "Outros"
                                      ORDER BY tbestado.uf, tbcidade.nome');

    array_unshift($listaCidade, array('*', '-- Todos --'));
    
    $relatorio->set_formCampos(array(
        array('nome' => 'cidade',
            'label' => 'Cidade:',
            'tipo' => 'combo',
            'array' => $listaCidade,
            'size' => 30,
            'padrao' => $cidade,
            'onChange' => 'formPadrao.submit();',
            'linha' => 1)));

    
    $relatorio->show();

    $page->terminaPagina();
}