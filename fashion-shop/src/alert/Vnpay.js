import { Toast, ToastContainer } from 'react-bootstrap';
import { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';

function PaymentToast({ message, status }) {
  const [show, setShow] = useState(true);

  return (
    <ToastContainer position="top-end" className="p-3">
      <AnimatePresence>
        {show && (
          <motion.div
            initial={{ opacity: 0, y: -30 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -30 }}
            transition={{ duration: 0.3 }}
          >
            <Toast
              bg={status === 'paid' ? 'success' : 'danger'}
              onClose={() => setShow(false)}
              show={true}
              delay={3000}
              autohide
            >
              <Toast.Body className="text-white">{message}</Toast.Body>
            </Toast>
          </motion.div>
        )}
      </AnimatePresence>
    </ToastContainer>
  );
}

export default PaymentToast;

