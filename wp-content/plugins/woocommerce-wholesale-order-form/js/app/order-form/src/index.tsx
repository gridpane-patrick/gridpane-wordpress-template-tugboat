import React from 'react';
import ReactDOM from 'react-dom';
import App from './App';

// ReactDOM.render(<App />, document.getElementById('root'));

// Find all DOM containers, and renderorder form into them.
document.querySelectorAll('.order_form')
  .forEach((domContainer: any) => {
    ReactDOM.render(
      <App attributes={JSON.parse(domContainer.attributes['data-order-form-attr'].value)} />,
      domContainer
    );
  });