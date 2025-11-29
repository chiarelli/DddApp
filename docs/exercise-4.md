# Exercise 4 — Conceitual

Branch: `exercise-4`

## Objetivos

É um exercício apenas conceitual, sem envolver criação de código.
Objetivo é saber se o canditato detém conhecimento das principais tecnologias que promovam a otimização de leitura em registros (de certas projeções) quando o número de usuários utilizando a aplicação simultâneamente aumenta e, consequentemente, a latência também. 

## Resposta

A solução depende da escala. Sem informações sobre volumetria, estado atual da infraestrutura e o conhecimento do nível de maturidade da equipe de dev e devOps sobre determinadas tecnologias, começaria pelas otimizações mais simples e, apenas se necessário, iria evoluindo a arquitetura. Em ordem de impacto e complexidade, eu adotaria:

### Nível 1 — Otimizações básicas 

Essas otimizações são **obrigatórias** e devem ser implementadas independemente de outras variáveis.

#### 1.1 Paginação e filtros no repositório

Não dá pra carregar “todos os clientes” quando a loja é grande. 
Eu colocaria paginação no repositório de leitura (ex.: page, pageSize) e, se necessário, filtros por nome/data.

#### 1.2 Melhorias no banco e consultas

- Criar uma view ou uma materialized view desnormalizada para acelerar a listagem.
- Criaria índices adequados e;
- Criaria SELECTs menores apenas com as colunas necessárias.

##### 1.2.1 Índices no MySQL (garantia de joins rápidos)

- customer_relationship_person: índice composto (customer_id, person_id). Se ordenar/filtrar por relationship, dá pra incluir (customer_id, relationship, person_id) como covering index.
- customer.id e person.id já são PK, ok. Se a listagem ordenar por birthdate ou created_at, criar índice nessas colunas melhora bastante.
- Pequena migração pra garantir esses índices custa barato e resolve os joins que mais doem.

#### 1.3 Reduzir colunas e custo de hidratação

- Pedir só as colunas necessárias (select específico) e, onde couber, usar asArray() pra evitar o custo de criar muitos objetos AR.
- Se precisar muita flexibilidade: joinWith + select das colunas exatas (customer.*, person.*, pivot.relationship) e mapear direto para DTOs. Já temos DTOs, então se encaixa bem.

> A realização dessas tarefas acima eliminam overhead que, em listas com milhares de linhas, pesa.

### Nível 2 — Cache e Estratégias de Leitura

Aqui já entram técnicas otimização de sistemas médios. O objetivo é usar Cache de leitura (cache-aside) e fragment cache.

- Como essa listagem muda pouco em relação ao número de leituras, dá pra adotar o pattern cache-aside no use case: tenta chave `customers:index:page=N:sort=…`; se não existir, busca no repositório e grava com TTL curto (ex.: 60–300s).
- No Yii, também dá pra usar fragment cache no bloco da tabela (views/customer/index.php), com chave por página/filtro. Como a view já só renderiza DTOs, é bem simples de plugar.
- Hoje o app usa FileCache, mas dá pra trocar o componente de cache pra MemCache (ou Redis se quiser algo mais robusto) e ganhar latência menor.

### Nível 3 — Mudanças de Arquitetura Interna

Aqui já entram estratégias em caso o volume de acessos simultâneos cresça **MUITO**.

#### 3.1 Read replica 

Quando a leitura começar a esmagar o banco, seria uma opção implementar leituras em réplicas, **se relatório não precisar ser 100% real-time**.

- Yii2 suporta master/slave nativo no componente `db (slaves => [...])`. Dá pra mandar as consultas do repositório de leitura para réplicas e deixar o master tranquilo para as escritas.
- Isso cresce throughput sem mexer quase nada no código de domínio.

#### 3.2 Read model desnormalizado / “materialized view”

O repositório de leitura passaria a consultar esta tabela com índices bem pensados; consultas ficam quase "instantâneas".

Manter uma tabela “achatada” (customer + relationship + person) atualizada por job/cron (ou eventos de domínio). Em MySQL não tem materialized view nativa, mas dá pra simular, ou pensar em usar outro banco relacional como **Postgres**.


### Técnica tranversal e outras duas muito avançadas

Abaixo discorro sobre as estratégias: 
- **CQRS** -> poderia ser implementado independentemente do nível de volumetria.
- **Escalonamento Vertical / Horizontal do Banco** -> técina muito avançada e complexa de implementar que somente faz sentido em um cenário de milhões de acessos simultâneos.

#### Refatorar para CQRS

Separar:
- Commands (CRUD, gravação)
- Queries (leitura otimizada)

> **Benefícios**: 
> - Queries podem usar repositórios diferentes (ex.: leitura em view desnormalizada).
> - Abre espaço para bancos especializados em leitura.

Já está separado leitura (CustomerReadRepositoryInterface) do resto. Dá pra evoluir pra uma consulta super otimizada (SQL puro, asArray, índices), sem tocar nas regras de domínio nem no restante do app. É pouca dor pra muito ganho.

#### Escalonamento Vertical / Horizontal do Banco

Técinas muito avançadas que **alteram profundamente a infraestrutura**. Dependem de refatoração extensa de código de produção e um nível de **maturidade alta** das equipes de dev e devOps.

##### Escalonamento Vertical (scale-up)

- Mais RAM
- Mais CPU
- Discos NVMe
- Ajuste de buffers do PostgreSQL/MySQL

> Simples, caro, mas funciona até certo ponto.

##### Escalonamento Horizontal (sharding)

Sharding (particionamento) por:

- ID do cliente
- Região
- Conta (em um SaaS faz muito sentido)

> Já é nível “empresa com milhões de registros”.