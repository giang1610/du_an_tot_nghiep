import React, { useState } from 'react';
import { FaStar } from 'react-icons/fa';

const InteractiveStarRating = ({ rating, onChange }) => {
    const [hover, setHover] = useState(0);

    return (
        <div className="d-flex">
            {Array.from({ length: 5 }, (_, index) => {
                const starValue = index + 1;
                return (
                    <FaStar
                        key={index}
                        size={28}
                        style={{ cursor: 'pointer', marginRight: 5 }}
                        color={starValue <= (hover || rating) ? '#ffc107' : '#e4e5e9'}
                        onClick={() => onChange(starValue)}
                        onMouseEnter={() => setHover(starValue)}
                        onMouseLeave={() => setHover(0)}
                    />
                );
            })}
        </div>
    );
};

export default InteractiveStarRating;
