# A light PHP http concurrent RPC library

## Why ConcurrentRPC

Sometimes, We need to request some other http address in order to get the data(we call it data api usually) in a application, As we know, tradition request in PHP is a serial way(one by one, eg: `curl`, `file_get_contents`) that is inefficient and affect user experience, even make the server down, So we need the ConcurrentRPC.

## Usage

### Push the requests what your need

```
$ConcurrentRPC = new ConcurrentRPC;
$request = $ConcurrentRPC
    ->get('http://some.one.get.url...', 'some-one-get-url-return-key')
    ->get('http://the.orthe.getr.url...', 'the-orther-get-url-return-key')
    ->post('http://some.one.post.url...', 'some-one-post-url-return-key', array('key' => 'value'));
```

### Receive the requests data

```
$result = $request->receive();
```

### Broadcast the requests but no receive any data

```
$request->broadcast();
```

see more demo in the `demo` directory.